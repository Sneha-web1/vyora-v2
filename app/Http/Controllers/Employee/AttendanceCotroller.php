<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Attendance\AttendanceRegisterReportController;
use App\Http\Controllers\Controller;
use App\Models\EmployeeAttendance;
use App\Models\Holiday;
use App\Models\Shift;
use App\Services\ShiftAttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceCotroller extends Controller
{
    /**
     * Current time in IST. Everything below is computed in IST wall-clock terms.
     */
    private static function istNow(): Carbon
    {
        return Carbon::now('Asia/Kolkata');
    }

    /**
     * Punch times are saved as IST wall-clock strings, but Eloquent hands them back labelled with the
     * app timezone (UTC). Re-read the wall-clock string as IST so diffs against "now" are exact.
     */
    private static function wall($dt): ?Carbon
    {
        return $dt ? Carbon::parse($dt->format('Y-m-d H:i:s'), 'Asia/Kolkata') : null;
    }

    /**
     * My Attendance dashboard: today's status + Punch In / Punch Out + live stats + filterable log.
     */
    public function index(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        if (!$employee) {
            return redirect()->route('employee.login');
        }

        $now = self::istNow();
        $todayDate = $now->toDateString();
        $shift = $employee->shift ?: ShiftAttendanceService::getDefaultShift();

        $todayAttendance = EmployeeAttendance::where('employee_id', $employee->employee_id)
            ->whereDate('attendance_date', $todayDate)
            ->first();

        $todayHours = 0;
        if ($todayAttendance && $todayAttendance->check_in_at) {
            $endTime = self::wall($todayAttendance->check_out_at) ?: $now;
            $todayHours = round(self::wall($todayAttendance->check_in_at)->diffInMinutes($endTime, true) / 60, 2);
        }

        [$from, $to] = $this->resolveRange($request, $now);

        $data['title'] = 'My Attendance';
        $data['employee'] = $employee;
        $data['shift'] = $shift;
        $data['todayAttendance'] = $todayAttendance;
        $data['todayHours'] = $todayHours;
        $data['now'] = $now;
        $data['stats'] = $this->buildStats($employee, $shift, $now);
        $data['filters'] = [
            'from'   => $from->toDateString(),
            'to'     => $to->toDateString(),
        ];

        return view('employee.attendance.index', $data);
    }

    /**
     * AJAX (DataTables server-side) source for the attendance log table.
     */
    public function list(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        if (!$employee) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $now = self::istNow();
        $shift = $employee->shift ?: ShiftAttendanceService::getDefaultShift();
        [$from, $to] = $this->resolveRange($request, $now);

        $rows = $this->buildRows($employee, $shift, $from, $to, $now);
        $recordsTotal = $rows->count();

        if (in_array($request->input('status'), ['Present', 'Absent', 'Week Off', 'Holiday'], true)) {
            $rows = $rows->where('status', $request->input('status'));
        }

        $search = trim((string) ($request->input('search')['value'] ?? ''));
        if ($search !== '') {
            $needle = mb_strtolower($search);
            $rows = $rows->filter(function ($r) use ($needle) {
                $hay = mb_strtolower(implode(' ', [
                    $r['date']->format('d M Y'), $r['date']->format('l'), $r['status'],
                    $r['check_in'] ?? '', $r['check_out'] ?? '', $r['note'] ?? '', $r['late_text'] ?? '',
                ]));
                return str_contains($hay, $needle);
            });
        }
        $recordsFiltered = $rows->count();

        // Ordering (only the sortable columns: 0 date, 2 status, 4 break, 5 late, 6 overtime, 7 hours)
        $orderCol = (int) ($request->input('order')[0]['column'] ?? 0);
        $desc = ($request->input('order')[0]['dir'] ?? 'desc') !== 'asc';
        $field = [0 => 'date_key', 2 => 'status', 4 => 'break_min', 5 => 'late_min', 6 => 'overtime_min', 7 => 'production_hours'][$orderCol] ?? 'date_key';
        $rows = $desc ? $rows->sortByDesc($field) : $rows->sortBy($field);

        $start = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 10);
        $page = $length > 0 ? $rows->slice($start, $length) : $rows->slice($start);

        $data = $page->values()->map(function ($r) {
            $badge = match ($r['status']) {
                'Present' => 'badge-success-transparent',
                'Absent'  => 'badge-danger-transparent',
                'Holiday' => 'badge-info-transparent',
                default   => 'badge-secondary-transparent',
            };
            $statusHtml = '<span class="badge ' . $badge . ' d-inline-flex align-items-center"><i class="ti ti-point-filled me-1"></i>' . e($r['status']) . '</span>';
            if (!empty($r['note'])) {
                $statusHtml .= '<div class="fs-11 text-muted">' . e($r['note']) . '</div>';
            }

            $outHtml = $r['check_out'] ? e($r['check_out']) : ($r['in_progress'] ? '<span class="text-info fs-12">In progress</span>' : '-');
            $hoursHtml = $r['production_hours'] !== null
                ? '<span class="badge badge-' . $r['hours_badge'] . ' d-inline-flex align-items-center"><i class="ti ti-clock-hour-11 me-1"></i>' . number_format($r['production_hours'], 2) . ' Hrs</span>'
                : '-';

            $breakTxt = $r['break_min'] ? $r['break_min'] . ' Min' : '-';
            $otTxt = $r['overtime_min'] ? $r['overtime_min'] . ' Min' : '-';
            $lateTxt = $r['late_text'] ?? '-';

            $actionHtml = '';
            if ($r['status'] === 'Present') {
                $actionHtml = '<a href="#" class="btn btn-icon btn-sm btn-light view-attendance" data-bs-toggle="modal" data-bs-target="#attendance_report"'
                    . ' data-date="' . e($r['date']->format('d M Y')) . '"'
                    . ' data-in="' . e($r['check_in']) . '"'
                    . ' data-out="' . e($r['check_out'] ?? '-') . '"'
                    . ' data-status="' . e($r['status']) . '"'
                    . ' data-hours="' . ($r['production_hours'] !== null ? number_format($r['production_hours'], 2) . ' Hrs' : '-') . '"'
                    . ' data-break="' . e($breakTxt) . '"'
                    . ' data-late="' . e($r['late_text'] ?? 'On Time') . '"'
                    . ' data-ot="' . e($otTxt) . '"><i class="ti ti-eye"></i></a>';
            }

            return [
                'date'      => $r['date']->format('d M Y'),
                'check_in'  => $r['check_in'] ?? '-',
                'status'    => $statusHtml,
                'check_out' => $outHtml,
                'break'     => $breakTxt,
                'late'      => $lateTxt,
                'overtime'  => $otTxt,
                'hours'     => $hoursHtml,
                'action'    => $actionHtml,
            ];
        });

        return response()->json([
            'draw'            => (int) $request->input('draw'),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    /**
     * Download the (filtered) attendance log as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $employee = Auth::guard('employee')->user();
        abort_unless($employee, 403);

        $now = self::istNow();
        $shift = $employee->shift ?: ShiftAttendanceService::getDefaultShift();
        [$from, $to] = $this->resolveRange($request, $now);

        $rows = $this->buildRows($employee, $shift, $from, $to, $now);
        if (in_array($request->input('status'), ['Present', 'Absent', 'Week Off', 'Holiday'], true)) {
            $rows = $rows->where('status', $request->input('status'));
        }
        $rows = $request->input('sort') === 'asc' ? $rows->sortBy('date_key') : $rows->sortByDesc('date_key');

        $filename = 'my-attendance-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Check In', 'Check Out', 'Status', 'Break (min)', 'Late (min)', 'Overtime (min)', 'Production Hours']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['date']->format('d M Y'),
                    $r['check_in'] ?: '-',
                    $r['check_out'] ?: '-',
                    $r['status'],
                    $r['break_min'] ?: '-',
                    $r['late_min'] ?: '-',
                    $r['overtime_min'] ?: '-',
                    $r['production_hours'] !== null ? $r['production_hours'] : '-',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Resolve the from/to range from ?from=&to= (defaults to the current month up to today).
     */
    private function resolveRange(Request $request, Carbon $now): array
    {
        try {
            $from = $request->filled('from') ? Carbon::parse($request->input('from'), 'Asia/Kolkata')->startOfDay() : $now->copy()->startOfMonth();
            $to   = $request->filled('to') ? Carbon::parse($request->input('to'), 'Asia/Kolkata')->endOfDay() : $now->copy()->endOfDay();
        } catch (\Throwable $e) {
            $from = $now->copy()->startOfMonth();
            $to   = $now->copy()->endOfDay();
        }

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }
        // Don't let someone request years of rows in one go.
        if ($from->diffInDays($to) > 366) {
            $from = $to->copy()->subDays(366)->startOfDay();
        }

        return [$from, $to];
    }

    /**
     * Build one row per calendar day in range: Present / Absent / Week Off / Holiday.
     */
    private function buildRows($employee, $shift, Carbon $from, Carbon $to, Carbon $now)
    {
        $records = EmployeeAttendance::where('employee_id', $employee->employee_id)
            ->whereBetween('attendance_date', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->keyBy(fn ($r) => $r->attendance_date->toDateString());

        $holidays = Holiday::whereBetween('holiday_date', [$from->toDateString(), $to->toDateString()])
            ->where(function ($q) {
                $q->where('status', 'Active')->orWhere('status', 1)->orWhere('status', '1');
            })
            ->get()
            ->keyBy(fn ($h) => Carbon::parse($h->holiday_date)->toDateString());

        $breakMin = (int) ($shift->break_duration ?? 0);
        $shiftSpanMin = Carbon::parse($shift->start_time ?? '09:30:00')->diffInMinutes(Carbon::parse($shift->end_time ?? '18:30:00'), true);

        $rows = collect();
        $lastDay = $to->copy()->min($now)->startOfDay(); // never list future days

        for ($d = $from->copy()->startOfDay(); $d->lte($lastDay); $d->addDay()) {
            $key = $d->toDateString();
            $rec = $records->get($key);

            $row = [
                'date'             => $d->copy(),
                'date_key'         => $key,
                'status'           => 'Absent',
                'check_in'         => null,
                'check_out'        => null,
                'break_min'        => 0,
                'late_min'         => 0,
                'late_text'        => null,
                'overtime_min'     => 0,
                'worked_min'       => 0,
                'production_hours' => null,
                'hours_badge'      => 'secondary',
                'in_progress'      => false,
            ];

            if ($rec && $rec->check_in_at) {
                $eval = ShiftAttendanceService::evaluate($rec->check_in_at, $rec->check_out_at, $shift, $key);
                $outAt = self::wall($rec->check_out_at) ?: ($d->isSameDay($now) ? $now : null);
                $worked = $outAt ? self::wall($rec->check_in_at)->diffInMinutes($outAt, true) : 0;
                $hasOut = (bool) $rec->check_out_at;
                $break = $hasOut ? min($breakMin, $worked) : 0;

                $row['status']       = 'Present';
                $row['check_in']     = $rec->check_in_at->format('h:i A');
                $row['check_out']    = $hasOut ? $rec->check_out_at->format('h:i A') : null;
                $row['in_progress']  = !$hasOut;
                $row['worked_min']   = (int) round($worked);
                $row['break_min']    = $break;
                $row['late_min']     = (int) $eval['late_minutes'];
                $row['late_text']    = $eval['is_late'] ? $eval['late_formatted'] : null;
                $row['overtime_min'] = $hasOut ? max(0, (int) round($worked - $shiftSpanMin)) : 0;
                $row['production_hours'] = $outAt ? round(max(0, $worked - $break) / 60, 2) : null;

                $target = (float) ($shift->total_working_hours ?: 8);
                $wh = $outAt ? $worked / 60 : null;
                $row['hours_badge'] = $wh === null ? 'secondary' : ($wh >= $target ? 'success' : ($wh >= $target - 1 ? 'info' : 'danger'));
            } elseif ($holidays->has($key)) {
                $row['status'] = 'Holiday';
                $row['note'] = $holidays->get($key)->holiday_name;
            } elseif (ShiftAttendanceService::isWeekOff($d, $employee, $shift)) {
                $row['status'] = 'Week Off';
            } elseif ($d->isSameDay($now)) {
                // Today with no punch yet is "not marked", not absent.
                $row['status'] = 'Absent';
                $row['note'] = 'Not marked yet';
            }

            $rows->push($row);
        }

        return $rows;
    }

    /**
     * Hours (production) between two dates for this employee.
     */
    private function productionHours($employee, $shift, Carbon $from, Carbon $to, Carbon $now): array
    {
        $rows = $this->buildRows($employee, $shift, $from, $to, $now);

        return [
            'hours'    => round($rows->sum(fn ($r) => $r['production_hours'] ?? 0), 2),
            'overtime' => round($rows->sum('overtime_min') / 60, 2),
            'workdays' => $rows->whereNotIn('status', ['Week Off', 'Holiday'])->count(),
        ];
    }

    /**
     * Top stat cards: today / this week / this month / overtime, with % change vs previous period.
     */
    private function buildStats($employee, $shift, Carbon $now): array
    {
        $daily = (float) ($shift->total_working_hours ?: 8);

        $weekStart = $now->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd   = $now->copy()->endOfWeek(Carbon::SUNDAY);
        $prevWeekStart = $weekStart->copy()->subWeek();
        $prevWeekEnd   = $weekEnd->copy()->subWeek();

        $monthStart = $now->copy()->startOfMonth();
        $monthEnd   = $now->copy()->endOfMonth();
        $prevMonthStart = $monthStart->copy()->subMonthNoOverflow()->startOfMonth();
        $prevMonthEnd   = $prevMonthStart->copy()->endOfMonth();

        // Targets count the whole period's working days (not just up to today).
        $workingDays = function (Carbon $a, Carbon $b) use ($employee, $shift) {
            $holidays = Holiday::whereBetween('holiday_date', [$a->toDateString(), $b->toDateString()])
                ->where(function ($q) {
                    $q->where('status', 'Active')->orWhere('status', 1)->orWhere('status', '1');
                })->pluck('holiday_date')->map(fn ($h) => Carbon::parse($h)->toDateString())->all();
            $n = 0;
            for ($d = $a->copy(); $d->lte($b); $d->addDay()) {
                if (!ShiftAttendanceService::isWeekOff($d, $employee, $shift) && !in_array($d->toDateString(), $holidays, true)) {
                    $n++;
                }
            }
            return $n;
        };

        $today = $this->productionHours($employee, $shift, $now->copy()->startOfDay(), $now->copy()->endOfDay(), $now);
        $week  = $this->productionHours($employee, $shift, $weekStart, $weekEnd, $now);
        $prevWeek = $this->productionHours($employee, $shift, $prevWeekStart, $prevWeekEnd, $now);
        $month = $this->productionHours($employee, $shift, $monthStart, $monthEnd, $now);
        $prevMonth = $this->productionHours($employee, $shift, $prevMonthStart, $prevMonthEnd, $now);

        $pct = function (float $cur, float $prev) {
            if ($prev <= 0) {
                return $cur > 0 ? 100.0 : 0.0;
            }
            return round((($cur - $prev) / $prev) * 100, 1);
        };

        return [
            'today' => ['value' => $today['hours'], 'target' => $daily],
            'week'  => ['value' => $week['hours'], 'target' => round($workingDays($weekStart, $weekEnd) * $daily, 1), 'change' => $pct($week['hours'], $prevWeek['hours'])],
            'month' => ['value' => $month['hours'], 'target' => round($workingDays($monthStart, $monthEnd) * $daily, 1), 'change' => $pct($month['hours'], $prevMonth['hours'])],
            'overtime' => ['value' => $month['overtime'], 'change' => $pct($month['overtime'], $prevMonth['overtime'])],
        ];
    }

    /**
     * AJAX: whole-month overview (totals + one bar per day) for the chart card.
     */
    public function monthChart(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        if (!$employee) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $now = self::istNow();
        $shift = $employee->shift ?: ShiftAttendanceService::getDefaultShift();

        try {
            $monthStart = Carbon::createFromFormat('Y-m', (string) $request->input('month', $now->format('Y-m')), 'Asia/Kolkata')->startOfMonth();
        } catch (\Throwable $e) {
            $monthStart = $now->copy()->startOfMonth();
        }
        if ($monthStart->gt($now)) {
            $monthStart = $now->copy()->startOfMonth();
        }
        $monthEnd = $monthStart->copy()->endOfMonth();

        $rows = $this->buildRows($employee, $shift, $monthStart, $monthEnd, $now)->keyBy('date_key');
        $target = (float) ($shift->total_working_hours ?: 9);

        $fmt = fn (int $m) => sprintf('%dh %02dm', intdiv($m, 60), $m % 60);

        $days = [];
        for ($d = $monthStart->copy(); $d->lte($monthEnd); $d->addDay()) {
            $r = $rows->get($d->toDateString());
            $worked = $r['worked_min'] ?? 0;
            $status = $r['status'] ?? 'Upcoming';
            $tip = $d->format('d M Y (D)') . ' - ' . $status;
            if ($status === 'Present') {
                $tip .= ' | ' . $r['check_in'] . ' to ' . ($r['check_out'] ?? 'in progress') . ' | ' . $fmt($worked);
            } elseif (!empty($r['note'])) {
                $tip .= ' | ' . $r['note'];
            }
            $days[] = [
                'day'    => (int) $d->format('j'),
                'dow'    => $d->format('D'),
                'status' => $status,
                'hours'  => round($worked / 60, 2),
                'tip'    => $tip,
            ];
        }

        $present = $rows->where('status', 'Present');
        $workedTotal = (int) $present->sum('worked_min');
        $breakTotal = (int) $present->sum('break_min');
        $overtimeTotal = (int) $present->sum('overtime_min');

        return response()->json([
            'label'  => $monthStart->format('F Y'),
            'month'  => $monthStart->format('Y-m'),
            'target' => $target,
            'totals' => [
                'total'      => $fmt($workedTotal),
                'productive' => $fmt(max(0, $workedTotal - $breakTotal)),
                'break'      => $fmt($breakTotal),
                'overtime'   => $fmt($overtimeTotal),
                'present'    => $present->count(),
                'absent'     => $rows->where('status', 'Absent')->count(),
                'late'       => $present->where('late_min', '>', 0)->count(),
            ],
            'days'   => $days,
        ]);
    }

    /**
     * Handle Web Punch In / Punch Out.
     */
    public function punch(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        if (!$employee) {
            return redirect()->route('employee.login');
        }

        if (strtolower((string) $employee->status) === 'inactive' || !empty($employee->deleted_at)) {
            return redirect()->back()->with('error', 'Your employee account is inactive. You cannot mark attendance.');
        }

        $now = self::istNow();
        $todayDate = $now->toDateString();

        $attendance = EmployeeAttendance::firstOrNew([
            'employee_id'     => $employee->employee_id,
            'attendance_date' => $todayDate,
        ]);

        if (!$attendance->exists || !$attendance->check_in_at) {
            // Punch In
            $attendance->check_in_at = $now;
            $attendance->check_in_ip = $request->ip();
            $attendance->check_in_user_agent = $request->userAgent();
            $attendance->status = 'checked_in';
            $attendance->save();

            return redirect()->back()->with('success', 'Checked In successfully at ' . $now->format('h:i A') . '!');
        }

        if (!$attendance->check_out_at) {
            // Punch Out
            $attendance->check_out_at = $now;
            $attendance->check_out_ip = $request->ip();
            $attendance->check_out_user_agent = $request->userAgent();
            $attendance->status = 'checked_out';
            $attendance->save();

            return redirect()->back()->with('success', 'Checked Out successfully at ' . $now->format('h:i A') . '!');
        }

        return redirect()->back()->with('warning', 'You have already checked in and checked out for today.');
    }

    /**
     * Display the logged-in employee's own Attendance Register (filterable matrix).
     */
    public function register(Request $request, AttendanceRegisterReportController $registerReport)
    {
        $employee = Auth::guard('employee')->user();
        if (!$employee) {
            return redirect()->route('employee.login');
        }

        $data = $registerReport->buildRegisterData($request, $employee->employee_id);
        $data['title'] = 'My Attendance Register';

        return view('employee.attendance.register', $data);
    }
}

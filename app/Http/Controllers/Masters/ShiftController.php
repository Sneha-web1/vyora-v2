<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShiftController extends Controller
{
    /**
     * Display shift listing page.
     */
    public function index()
    {
        $data['title'] = 'Shift Master';
        $data['imageUrl'] = "https://picsum.photos/200/200?random=" . rand(1, 1000);

        // KPI metrics
        $data['totalShifts']    = Shift::count();
        $data['activeShifts']   = Shift::where('status', 'Active')->count();
        $data['inactiveShifts'] = Shift::where('status', 'Inactive')->count();
        $data['assignedStaff']  = Employee::whereNotNull('shift_id')->where('shift_id', '>', 0)->count();

        return view('home.shift.index', $data);
    }

    /**
     * Return Shift list for DataTable (AJAX).
     */
    public function list(Request $request)
    {
        try {
            $search = $request->input('search')['value'] ?? null;
            $limit  = (int) $request->input('length', 10);
            $start  = (int) $request->input('start', 0);

            $totalRecord = Shift::count();

            $query = Shift::query()->withCount('employees');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('shift_name', 'like', "%{$search}%")
                        ->orWhere('shift_code', 'like', "%{$search}%");
                });
            }
            $filteredRecord = $query->count();

            $shifts = $query->orderBy('id', 'asc')
                ->skip($start)
                ->take($limit > 0 ? $limit : 100)
                ->get();

            $rows = [];
            foreach ($shifts as $index => $shift) {
                $days = $shift->week_off_days ?: ['Sunday'];
                if (!is_array($days)) {
                    $days = json_decode($days, true) ?: ['Sunday'];
                }
                $dayBadges = '';
                foreach ($days as $day) {
                    $dayBadges .= '<span class="badge bg-warning-subtle text-warning border border-warning-subtle me-1">' . e($day) . '</span>';
                }

                $staffCount = (int) $shift->employees_count;
                $staffBadge = $staffCount . ' Staff';

                $isActive = $shift->status === 'Active';
                $statusBadge = $isActive
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';

                $rows[] = [
                    'id'                  => $shift->id,
                    'DT_RowIndex'         => $start + $index + 1,
                    'shift_name'          => e($shift->shift_name),
                    'shift_code'          => e($shift->shift_code),
                    'start_time'          => date('h:i A', strtotime($shift->start_time)),
                    'end_time'            => date('h:i A', strtotime($shift->end_time)),
                    'week_off_days'       => $dayBadges ?: '<span class="badge bg-light text-muted border">None</span>',
                    'break_duration'      => ($shift->break_duration ?? 0) . ' min',
                    'grace_period'        => ($shift->grace_period ?? 15) . ' min',
                    'total_working_hours' => $shift->total_working_hours . ' hrs',
                    'staff_count'         => $staffBadge,
                    'staff_raw'           => $staffCount,
                    'status'              => $statusBadge,
                    'is_active'           => $isActive,
                ];
            }

            return response()->json([
                'draw'            => intval($request->input('draw')),
                'recordsTotal'    => $totalRecord,
                'recordsFiltered' => $filteredRecord,
                'data'            => $rows,
            ]);
        } catch (\Exception $e) {
            Log::error('Shift List Error: ' . $e->getMessage());
            return response()->json([
                'error'     => true,
                'message'   => 'Something went wrong while fetching shift records.',
                'exception' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show create shift form.
     */
    public function create()
    {
        $data['title'] = 'Master / Organisation / Shift Create';
        $data['imageUrl'] = "https://picsum.photos/200/200?random=" . rand(1, 1000);

        return view('home.shift.create', $data);
    }

    /**
     * Store a new shift.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'shift_name' => 'required|string|max:255',
                'shift_code' => 'required|string|max:50|unique:shifts,shift_code',
                'start_time' => 'required',
                'end_time' => 'required',
                'week_off_days' => 'nullable|array',
                'break_duration' => 'nullable|integer|min:0',
                'total_working_hours' => 'nullable|numeric|min:0',
                'grace_period' => 'nullable|integer|min:0',
                'status' => 'required|in:Active,Inactive',
            ]);

            Shift::create([
                'shift_name' => $request->shift_name,
                'shift_code' => $request->shift_code,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'week_off_days' => $request->week_off_days ?? ['Sunday'],
                'break_duration' => $request->break_duration ?? 60,
                'total_working_hours' => $request->total_working_hours ?? 8.00,
                'grace_period' => $request->grace_period ?? 15,
                'status' => $request->status,
            ]);

            return redirect()
                ->route('masters.organisation.shift')
                ->with('success', 'Shift created successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {

            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {

            return redirect()->back()
                ->with('error', 'Something went wrong: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show edit shift form.
     */
    public function edit(string $id)
    {
        $data['title'] = 'Master / Organisation / Shift Edit';
        $data['shift'] = Shift::findOrFail($id);
        $data['imageUrl'] = "https://picsum.photos/200/200?random=" . rand(1, 1000);

        return view('home.shift.edit', $data);
    }

    /**
     * Update shift record.
     */
    public function update(Request $request, string $id)
    {
        $shift = Shift::findOrFail($id);

        $request->validate([
            'shift_name' => 'required|string|max:255',
            'shift_code' => 'required|string|max:50|unique:shifts,shift_code,' . $shift->id,
            'start_time' => 'required',
            'end_time' => 'required',
            'week_off_days' => 'nullable|array',
            'break_duration' => 'nullable|integer|min:0',
            'total_working_hours' => 'nullable|numeric|min:0',
            'grace_period' => 'nullable|integer|min:0',
            'status' => 'required|in:Active,Inactive',
        ]);

        $shift->update([
            'shift_name' => $request->shift_name,
            'shift_code' => $request->shift_code,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'week_off_days' => $request->input('week_off_days', []),
            'break_duration' => $request->break_duration ?? 60,
            'total_working_hours' => $request->total_working_hours ?? 8.00,
            'grace_period' => $request->grace_period ?? 15,
            'status' => $request->status,
        ]);

        return redirect()->route('masters.organisation.shift')
            ->with('success', 'Shift updated successfully!');
    }

    /**
     * Toggle shift status Active <-> Inactive.
     */
    public function toggleStatus(string $id)
    {
        $shift = Shift::find($id);

        if (!$shift) {
            return response()->json(['success' => false, 'message' => 'Shift not found.'], 404);
        }

        $shift->status = $shift->status === 'Active' ? 'Inactive' : 'Active';
        $shift->save();

        return response()->json([
            'success' => true,
            'status'  => $shift->status,
            'message' => "Shift '{$shift->shift_name}' is now {$shift->status}.",
        ]);
    }

    /**
     * Delete shift (blocked while staff are assigned).
     */
    public function destroy(string $id)
    {
        $shift = Shift::find($id);

        if (!$shift) {
            return response()->json(['success' => false, 'message' => 'Shift not found.'], 404);
        }

        try {
            $staffCount = Employee::where('shift_id', $shift->id)->count();

            if ($staffCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete shift: {$staffCount} employee(s) are assigned to it. Please reassign them or mark the shift Inactive instead.",
                ], 422);
            }

            $shift->delete();
            return response()->json(['success' => true, 'message' => 'Shift deleted successfully!']);
        } catch (\Exception $e) {
            Log::error('Shift Delete Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Something went wrong while deleting: ' . $e->getMessage()], 500);
        }
    }
}
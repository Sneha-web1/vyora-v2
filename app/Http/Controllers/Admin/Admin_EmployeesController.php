<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Country;
use App\Models\CountryState;
use App\Models\StateCity;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeFamilyMember;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class Admin_EmployeesController extends Controller
{


    public function index(Request $request)
    {
        $data['title'] = 'Employee Directory';

        $totalEmployees = Employee::whereNull('deleted_at')->count();
        $activeEmployees = Employee::whereNull('deleted_at')
            ->where(function ($q) {
                $q->where('status', 'Active')
                    ->orWhere('status', '1')
                    ->orWhereNull('status')
                    ->orWhere('status', '');
            })->count();
        $inactiveEmployees = Employee::whereNull('deleted_at')
            ->where('status', 'Inactive')
            ->count();
        $fulltimeEmployees = Employee::whereNull('deleted_at')
            ->where(function ($q) {
                $q->where('employment_type', 'like', '%Full%');
            })->count();

        $data['totalEmployees'] = $totalEmployees;
        $data['activeEmployees'] = $activeEmployees;
        $data['inactiveEmployees'] = $inactiveEmployees;
        $data['fulltimeEmployees'] = $fulltimeEmployees;
        $data['agencies'] = Agency::where('status', 'Active')->orderBy('agency_name')->get();
        $data['designations'] = Designation::where('status', 'Active')->orderBy('name')->get();
        $data['selectedStatus'] = $request->input('status', 'Active');

        if ($request->ajax()) {
            // Server-side DataTables (same contract as Company / Branch lists)
            $search = $request->input('search')['value'] ?? null;
            $limit  = (int) $request->input('length', 10);
            $start  = (int) $request->input('start', 0);

            $query = Employee::with(['designation', 'agency'])->whereNull('deleted_at');

            if ($request->input('status') === 'Inactive') {
                $query->where('status', 'Inactive');
            } elseif ($request->input('status') === 'Active') {
                $query->where(function ($q) {
                    $q->where('status', '!=', 'Inactive')->orWhereNull('status');
                });
            }

            if ($request->filled('agency_id')) {
                if ($request->agency_id === 'direct') {
                    $query->whereNull('agency_id');
                } else {
                    $query->where('agency_id', $request->agency_id);
                }
            }

            if ($request->filled('designation_id')) {
                $query->where('designation_id', $request->designation_id);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('employee_name', 'like', "%{$search}%")
                        ->orWhereEmployeeContact($search);
                });
            }

            $totalRecord = $query->count();
            $employees   = $query->orderByDesc('employee_id')->skip($start)->take($limit)->get();

            $rows = [];
            foreach ($employees as $index => $emp) {
                $isActive = $emp->status !== 'Inactive';
                $id       = $emp->employee_id;
                $agency   = $emp->agency
                    ? e($emp->agency->agency_name) . ($emp->agency->agency_code ? ' (' . e($emp->agency->agency_code) . ')' : '')
                    : 'Direct / In-House';
                $designation = $emp->designation->name ?? ($emp->designation->designation_name ?? 'Staff');
                $joining = $emp->joining_date ? Carbon::parse($emp->joining_date)->format('d M, Y') : '--';

                $rows[] = [
                    'DT_RowIndex' => $start + $index + 1,
                    'employee'    => '<div><span class="fw-medium">' . e($emp->employee_name) . '</span>'
                        . '<div class="text-muted fs-12">' . e($emp->masked_email ?: '--') . '</div></div>',
                    'agency'      => $agency,
                    'designation' => e($designation),
                    'joining_date' => $joining,
                    'status'      => $isActive
                        ? '<span class="badge bg-success" style="cursor:pointer;" title="Click to change status" onclick="openStatusModal(' . $id . ', \'Active\')">Active</span>'
                        : '<span class="badge bg-danger" style="cursor:pointer;" title="Click to change status" onclick="openStatusModal(' . $id . ', \'Inactive\')">Inactive</span>',
                    'action'      => '<div class="action-icon d-inline-flex">'
                        . '<a href="javascript:void(0);" onclick="viewEmployee(' . $id . ')" class="me-2" title="View"><i class="ti ti-eye"></i></a>'
                        . '<a href="' . url('employees/edit/' . $id) . '" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>'
                        . '<a href="javascript:void(0);" onclick="deleteEmployee(' . $id . ')" title="Delete"><i class="ti ti-trash"></i></a>'
                        . '</div>',
                ];
            }

            return response()->json([
                'draw'            => intval($request->input('draw')),
                'recordsTotal'    => $totalRecord,
                'recordsFiltered' => $totalRecord,
                'data'            => $rows,
            ]);
        }

        return view('admin.employee.index', $data);
    }

    public function updateStatus(Request $request, $employee_id)
    {
        $employee = Employee::where('employee_id', $employee_id)->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found'
            ]);
        }

        if ($request->status == 1 || $request->status === 'Active' || $request->status === '1') {
            $employee->activate();
        } else {
            $employee->deactivate();
        }

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully'
        ]);
    }


    public function create()
    {
        $data['title'] = 'Add Employee';
        $data['countries'] = Country::all();
        $data['designations'] = Designation::where('status', 'Active')->get();
        $data['agencies'] = Agency::where('status', 'Active')->orderBy('agency_name')->get();
        $data['shifts'] = Shift::where('status', 'Active')->orderBy('shift_name')->get();
        $data['supervisors'] = Employee::where(function ($q) {
            $q->where('is_supervisor', 1)
              ->orWhereHas('designation', function ($d) {
                  $d->where('name', 'like', '%Supervisor%');
              });
        })->where('status', 'Active')->orderBy('employee_name')->get();

        return view('admin.employee.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_name' => 'required|string|max:255',
            'employee_email' => ['required', 'email', new \App\Rules\UniqueEmployeeEmail()],
            'employee_mobile' => 'required',
            'employee_password' => 'required|string|min:6',
            'employee_gender' => 'required|in:1,2,3',
            'blood_group' => 'nullable|string|max:10',
            'posting_country' => 'required',
            'posting_state' => 'required',
            'posting_city' => 'required',
            'joining_date' => 'required|date',
            'employment_type' => 'required',
            'designation_id' => 'required',
            'agency_id' => 'nullable|exists:agencies,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'is_supervisor' => 'nullable',
            'supervisor_id' => 'nullable|exists:employees,employee_id',
            'status' => 'required',
            'profile_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'address' => 'required|string|max:1000',
            'notice_period_days' => 'required|integer|min:0|max:180',
        ] + $this->familyRules(), $this->extraMessages(), $this->extraAttributes());

        $image_name = null;
        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $image_name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/employees'), $image_name);
        }

        $isSupervisor = $request->boolean('is_supervisor') || $request->input('is_supervisor') === '1' || $request->input('is_supervisor') === 'on';
        $designation = Designation::find($request->designation_id);
        if ($designation && stripos($designation->name, 'Supervisor') !== false) {
            $isSupervisor = true;
        }

        $supervisorId = $request->filled('supervisor_id') ? $request->supervisor_id : null;

        DB::transaction(function () use ($request, $image_name, $isSupervisor, $supervisorId) {
        $employee = Employee::create([
            'employee_name' => $request->employee_name,
            'employee_email' => $request->employee_email,
            'employee_mobile' => $request->employee_mobile,
            'employee_password' => bcrypt($request->employee_password),
            'employee_gender' => $request->employee_gender,
            'blood_group' => $request->blood_group ?: null,
            'posting_country' => $request->posting_country,
            'posting_state' => $request->posting_state,
            'posting_city' => $request->posting_city,
            'joining_date' => $request->joining_date,
            'date_of_confirmation' => $request->date_of_confirmation,
            'employment_type' => $request->employment_type,
            'designation_id' => $request->designation_id,
            'agency_id' => $request->agency_id ?: null,
            'shift_id' => $request->shift_id ?: (Shift::where('status', 'Active')->value('id') ?? 1),
            'week_off_days' => $request->filled('week_off_days') ? array_values((array)$request->input('week_off_days')) : null,
            'is_supervisor' => $isSupervisor ? 1 : 0,
            'supervisor_id' => $supervisorId,
            'status' => $request->status,
            'profile_image' => $image_name,
            'address' => $request->address,
            'notice_period_days' => (int) $request->notice_period_days,
        ]);

        $this->syncFamilyMembers($employee, (array) $request->input('family_members', []));
        });

        return redirect()->route('employees.index')->with('success', 'Employee Added Successfully');
    }

    /** Bulk import (CSV / XLSX). Needs the maatwebsite/excel (phpspreadsheet) package. */
    public function import(Request $request)
    {
        $request->validate([
            'employee_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ], [
            'employee_file.required' => 'Please select a CSV or Excel file to upload.',
            'employee_file.mimes' => 'The import file must be a CSV or Excel file (.csv, .xlsx, .xls).',
            'employee_file.max' => 'The file size must not exceed 10 MB.',
        ]);

        try {
            $rows = $this->readImportRows($request->file('employee_file')->getRealPath());
        } catch (\Throwable $e) {
            return redirect()->route('employees.index')->with('error', 'Could not read the file: ' . $e->getMessage());
        }

        if (count($rows) < 2) {
            return redirect()->route('employees.index')->with('error', 'The file has no data rows.');
        }

        $headers = array_map(fn ($h) => $this->mapCanonicalHeader($this->normalizeHeader($h)), $rows[0]);
        $created = 0;
        $errors = [];

        foreach (array_slice($rows, 1) as $i => $row) {
            $line = $i + 2;
            $rec = [];
            foreach ($headers as $idx => $field) {
                if ($field && isset($row[$idx]) && $row[$idx] !== '') {
                    $rec[$field] = is_string($row[$idx]) ? trim($row[$idx]) : $row[$idx];
                }
            }

            if (empty($rec['employee_name']) || empty($rec['employee_email'])) {
                $errors[] = "Row {$line}: name and email are required.";
                continue;
            }
            if (!filter_var($rec['employee_email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Row {$line}: invalid email ({$rec['employee_email']}).";
                continue;
            }
            if (Employee::withTrashed()->whereEmailIs($rec['employee_email'])->exists()) {
                $errors[] = "Row {$line}: {$rec['employee_email']} already exists.";
                continue;
            }

            $countryId = $this->resolveCountry($rec['posting_country'] ?? null);
            $stateId = $this->resolveState($rec['posting_state'] ?? null, $countryId);
            $designationId = $this->resolveDesignation($rec['designation_id'] ?? null);

            try {
                Employee::create([
                    'employee_name' => $rec['employee_name'],
                    'employee_email' => $rec['employee_email'],
                    'employee_mobile' => $rec['employee_mobile'] ?? '',
                    'employee_password' => bcrypt($rec['employee_password'] ?? 'Welcome@123'),
                    'employee_gender' => $this->normalizeGender($rec['employee_gender'] ?? null),
                    'blood_group' => $this->normalizeBloodGroup($rec['blood_group'] ?? null),
                    'posting_country' => $countryId,
                    'posting_state' => $stateId,
                    'posting_city' => $this->resolveCity($rec['posting_city'] ?? null, $stateId),
                    'joining_date' => $this->normalizeImportDate($rec['joining_date'] ?? null) ?? now()->toDateString(),
                    'date_of_confirmation' => $this->normalizeImportDate($rec['date_of_confirmation'] ?? null),
                    'employment_type' => $this->normalizeEmploymentType($rec['employment_type'] ?? null),
                    'designation_id' => $designationId,
                    'agency_id' => $this->resolveAgency($rec['agency_id'] ?? null),
                    'shift_id' => $this->resolveShift($rec['shift_id'] ?? null),
                    'is_supervisor' => $this->resolveSupervisorFlag($rec['is_supervisor'] ?? null, $designationId),
                    'supervisor_id' => $this->resolveSupervisor($rec['supervisor_id'] ?? null, $rec['employee_email']),
                    'status' => (strtolower((string) ($rec['status'] ?? 'Active')) === 'inactive') ? 'Inactive' : 'Active',
                    'profile_image' => null,
                ]);
                $created++;
            } catch (\Throwable $e) {
                $errors[] = "Row {$line}: " . $e->getMessage();
            }
        }

        $redirect = redirect()->route('employees.index')->with('success', "{$created} employee(s) imported successfully.");
        if ($errors) {
            $preview = implode('<br>', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $preview .= '<br>...and ' . (count($errors) - 5) . ' more skipped row(s).';
            }
            $redirect->with('warning', count($errors) . " row(s) were skipped:<br>{$preview}");
        }

        return $redirect;
    }

    public function importSample()
    {
        $headers = ['employee_name', 'employee_email', 'employee_mobile', 'employee_password', 'gender', 'blood_group', 'country', 'state', 'city', 'joining_date', 'employment_type', 'designation', 'agency', 'shift', 'is_supervisor', 'supervisor', 'status'];
        $sample = ['Rahul Sharma', 'rahul@example.com', '9876543210', 'Welcome@123', 'Male', 'B+', 'India', 'Uttar Pradesh', 'Kanpur', date('Y-m-d'), 'Full Time', 'Developer', '', 'General Shift', 'No', '', 'Active'];

        return response()->streamDownload(function () use ($headers, $sample) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            fputcsv($out, $sample);
            fclose($out);
        }, 'employee_import_sample.csv', ['Content-Type' => 'text/csv']);
    }

    private function mapCanonicalHeader(string $header): ?string
    {
        return match ($header) {
            'employee_name', 'name', 'full_name', 'fullname', 'emp_name' => 'employee_name',
            'employee_email', 'email', 'email_address', 'mail', 'emp_email' => 'employee_email',
            'employee_mobile', 'mobile', 'phone', 'contact', 'mobile_number', 'phone_number', 'contact_number' => 'employee_mobile',
            'employee_password', 'password', 'pass' => 'employee_password',
            'employee_gender', 'gender', 'sex' => 'employee_gender',
            'blood_group', 'bloodgroup', 'blood', 'blood_type' => 'blood_group',
            'posting_country', 'country', 'country_id', 'country_name' => 'posting_country',
            'posting_state', 'state', 'state_id', 'state_name' => 'posting_state',
            'posting_city', 'city', 'city_id', 'city_name' => 'posting_city',
            'joining_date', 'date_of_joining', 'doj', 'joining' => 'joining_date',
            'date_of_confirmation', 'confirmation_date', 'doc' => 'date_of_confirmation',
            'employment_type', 'type', 'job_type' => 'employment_type',
            'designation_id', 'designation', 'designation_name', 'role', 'position' => 'designation_id',
            'agency_id', 'agency', 'agency_name', 'agency_code', 'contractor', 'assigned_agency' => 'agency_id',
            'shift_id', 'shift', 'shift_name', 'work_shift' => 'shift_id',
            'is_supervisor', 'supervisor', 'mark_as_supervisor', 'is_supervisor_flag' => 'is_supervisor',
            'supervisor_id', 'reporting_supervisor', 'reports_to', 'manager', 'supervisor_name', 'supervisor_email' => 'supervisor_id',
            'status', 'employee_status', 'account_status' => 'status',
            'profile_image', 'image', 'photo', 'avatar' => 'profile_image',
            default => null,
        };
    }

    private function readImportRows(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();

        return $sheet->toArray(null, true, true, false);
    }

    private function normalizeHeader($header): string
    {
        return strtolower(trim(str_replace([' ', '-', '/'], '_', (string) $header)));
    }

    private function normalizeGender($gender): int
    {
        $value = strtolower(trim((string) $gender));

        return match ($value) {
            '2', 'female', 'f', 'woman' => 2,
            '3', 'other', 'o' => 3,
            default => 1, // Male
        };
    }

    private function normalizeImportDate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }

    private function resolveCountry($value)
    {
        if (empty($value)) {
            return Country::value('id') ?? 101;
        }

        if (is_numeric($value) && Country::where('id', $value)->exists()) {
            return (int) $value;
        }

        $country = Country::where('name', 'like', "%{$value}%")->orWhere('code', 'like', "%{$value}%")->first();
        return $country ? $country->id : (Country::value('id') ?? 101);
    }

    private function resolveState($value, $countryId = null)
    {
        if (empty($value)) {
            return CountryState::when($countryId, fn($q) => $q->where('country_id', $countryId))->value('id') ?? 1;
        }

        if (is_numeric($value) && CountryState::where('id', $value)->exists()) {
            return (int) $value;
        }

        $state = CountryState::when($countryId, fn($q) => $q->where('country_id', $countryId))
            ->where(function ($q) use ($value) {
                $q->where('name', 'like', "%{$value}%")
                    ->orWhere('code', 'like', "%{$value}%");
            })->first();

        if (!$state) {
            $state = CountryState::where('name', 'like', "%{$value}%")->first();
        }

        return $state ? $state->id : (CountryState::when($countryId, fn($q) => $q->where('country_id', $countryId))->value('id') ?? 1);
    }

    private function resolveCity($value, $stateId = null)
    {
        if (empty($value)) {
            return StateCity::when($stateId, fn($q) => $q->where('state_id', $stateId))->value('id') ?? 1;
        }

        if (is_numeric($value) && StateCity::where('id', $value)->exists()) {
            return (int) $value;
        }

        $city = StateCity::when($stateId, fn($q) => $q->where('state_id', $stateId))
            ->where(function ($q) use ($value) {
                $q->where('name', 'like', "%{$value}%")
                    ->orWhere('code', 'like', "%{$value}%");
            })->first();

        if (!$city) {
            $city = StateCity::where('name', 'like', "%{$value}%")->first();
        }

        return $city ? $city->id : (StateCity::when($stateId, fn($q) => $q->where('state_id', $stateId))->value('id') ?? 1);
    }

    private function resolveDesignation($value)
    {
        if (empty($value)) {
            return Designation::where('status', 'Active')->value('id') ?? Designation::value('id') ?? 1;
        }

        if (is_numeric($value) && Designation::where('id', $value)->exists()) {
            return (int) $value;
        }

        $desig = Designation::where('name', 'like', "%{$value}%")
            ->orWhere('code', 'like', "%{$value}%")
            ->first();

        return $desig ? $desig->id : (Designation::where('status', 'Active')->value('id') ?? 1);
    }

    private function resolveAgency($value): ?int
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value) && Agency::where('id', $value)->exists()) {
            return (int) $value;
        }

        $agency = Agency::where('agency_name', 'like', "%{$value}%")
            ->orWhere('agency_code', 'like', "%{$value}%")
            ->first();

        return $agency ? $agency->id : null;
    }

    private function resolveShift($value): int
    {
        if (empty($value)) {
            return Shift::where('status', 'Active')->value('id') ?? 1;
        }

        if (is_numeric($value) && Shift::where('id', $value)->exists()) {
            return (int) $value;
        }

        $shift = Shift::where('shift_name', 'like', "%{$value}%")->first();

        return $shift ? $shift->id : (Shift::where('status', 'Active')->value('id') ?? 1);
    }

    private function resolveSupervisor($value, ?string $employeeEmail = null): ?int
    {
        if (empty($value) || in_array(strtolower(trim((string)$value)), ['none', 'no', 'null', 'n/a', '-', '0'], true)) {
            return null;
        }

        if (is_numeric($value) && Employee::where('employee_id', $value)->exists()) {
            return (int) $value;
        }

        $cleanVal = trim((string) $value);

        // Search among supervisors first
        $supervisor = Employee::where('is_supervisor', 1)
            ->where(function ($q) use ($cleanVal) {
                $q->where('employee_name', 'like', "%{$cleanVal}%")
                  ->orWhereEmployeeContact($cleanVal);
            })
            ->first();

        if ($supervisor) {
            return $supervisor->employee_id;
        }

        // Search any employee by name, email, or mobile (excluding current employee)
        $emp = Employee::where(function ($q) use ($cleanVal) {
                $q->where('employee_name', 'like', "%{$cleanVal}%")
                  ->orWhereEmployeeContact($cleanVal);
            })
            ->when($employeeEmail, fn($q) => $q->where(fn($w) => $w->where('employee_email_bidx', '!=', Employee::blindIndex($employeeEmail, 'email'))->orWhereNull('employee_email_bidx')))
            ->first();

        return $emp ? $emp->employee_id : null;
    }

    private function resolveSupervisorFlag($value, $designationId = null): int
    {
        if ($designationId) {
            $desig = Designation::find($designationId);
            if ($desig && stripos($desig->name, 'Supervisor') !== false) {
                return 1;
            }
        }

        $val = strtolower(trim((string) $value));
        if (in_array($val, ['1', 'yes', 'y', 'true', 'on', 'supervisor'], true)) {
            return 1;
        }

        return 0;
    }

    private function normalizeBloodGroup($value): ?string
    {
        if (empty($value) || $value === '-') {
            return null;
        }

        $clean = strtoupper(trim(str_replace(' ', '', (string) $value)));
        $validGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];

        if (in_array($clean, $validGroups, true)) {
            return $clean;
        }

        return in_array($clean, ['A', 'B', 'AB', 'O'], true) ? $clean . '+' : null;
    }

    private function normalizeEmploymentType($value): string
    {
        if (empty($value)) {
            return 'Full Time';
        }

        $val = strtolower(trim((string) $value));

        return match (true) {
            str_contains($val, 'part') => 'Part Time',
            str_contains($val, 'contract') => 'Contract',
            str_contains($val, 'intern') => 'Intern',
            default => 'Full Time',
        };
    }

    public function edit($employee_id)
    {
        $employee = Employee::with('familyMembers')->where('employee_id', $employee_id)->firstOrFail();

        $data['title'] = 'Edit Employee';
        $data['employee'] = $employee;
        $data['countries'] = Country::all();
        $data['designations'] = Designation::where('status', 'Active')->get();
        $data['agencies'] = Agency::where('status', 'Active')->orderBy('agency_name')->get();
        $data['shifts'] = Shift::where('status', 'Active')->orderBy('shift_name')->get();
        $data['states'] = CountryState::where('country_id', $employee->posting_country)->get();
        $data['cities'] = StateCity::where('state_id', $employee->posting_state)->get();

        $data['supervisors'] = Employee::where(function ($q) {
            $q->where('is_supervisor', 1)
              ->orWhereHas('designation', function ($d) {
                  $d->where('name', 'like', '%Supervisor%');
              });
        })->where('employee_id', '!=', $employee_id)
          ->where('status', 'Active')
          ->orderBy('employee_name')
          ->get();

        // Mapped subordinates under this employee if they are a supervisor
        $data['subordinates'] = Employee::with(['designation', 'agency'])
            ->where('supervisor_id', $employee_id)
            ->whereNull('deleted_at')
            ->get();

        // Active employees available to be mapped to this supervisor
        $data['availableEmployees'] = Employee::with(['designation', 'agency'])
            ->where('employee_id', '!=', $employee_id)
            ->where(function ($q) use ($employee_id) {
                $q->whereNull('supervisor_id')
                  ->orWhere('supervisor_id', '!=', $employee_id);
            })
            ->where('status', 'Active')
            ->whereNull('deleted_at')
            ->orderBy('employee_name')
            ->get();

        return view('admin.employee.edit', $data);
    }

    public function update(Request $request, $employee_id)
    {
        $employee = Employee::where('employee_id', $employee_id)->firstOrFail();

        $request->validate([
            'employee_name'   => 'required|string|max:255',
            'employee_email'  => ['required', 'email', new \App\Rules\UniqueEmployeeEmail((int) $employee_id)],
            'employee_mobile' => 'required',
            'employee_gender' => 'required',
            'blood_group'     => 'nullable|string|max:10',
            'joining_date'    => 'required|date',
            'employment_type' => 'required',
            'designation_id'  => 'required',
            'agency_id'       => 'nullable|exists:agencies,id',
            'shift_id'        => 'nullable|exists:shifts,id',
            'is_supervisor'   => 'nullable',
            'supervisor_id'   => 'nullable|exists:employees,employee_id',
            'status'          => 'required',
            'address'         => 'required|string|max:1000',
            'notice_period_days' => 'required|integer|min:0|max:180',
        ] + $this->familyRules() + $this->dpdpRules(false, $request->boolean('dpdp_consent_given') && !$employee->dpdp_consent_given),
        $this->extraMessages(), $this->extraAttributes());

        // consent fields & family rows are handled explicitly below, never mass-assigned from the request
        $data = $request->except([
            'family_members', 'dpdp_privacy_notice_ack', 'dpdp_consent_given', 'dpdp_sharing_consent',
            'dpdp_consent_mode', 'dpdp_consent_date', 'dpdp_consent_at', 'dpdp_consent_version',
            'dpdp_consent_ip', 'dpdp_consent_recorded_by',
        ]);
        $data['agency_id'] = $request->filled('agency_id') ? $request->agency_id : null;
        $data['shift_id'] = $request->filled('shift_id') ? $request->shift_id : ($employee->shift_id ?: (Shift::where('status', 'Active')->value('id') ?? 1));
        $data['week_off_days'] = $request->filled('week_off_days') ? array_values((array)$request->input('week_off_days')) : null;
        $data['blood_group'] = $request->filled('blood_group') ? $request->blood_group : null;

        $isSupervisor = $request->boolean('is_supervisor') || $request->input('is_supervisor') === '1' || $request->input('is_supervisor') === 'on';
        $designation = Designation::find($request->designation_id);
        if ($designation && stripos($designation->name, 'Supervisor') !== false) {
            $isSupervisor = true;
        }

        $data['is_supervisor'] = $isSupervisor ? 1 : 0;
        $data['supervisor_id'] = $request->filled('supervisor_id') ? $request->supervisor_id : null;

        if ($request->filled('employee_password')) {
            $data['employee_password'] = bcrypt($request->employee_password);
        } else {
            unset($data['employee_password']);
        }
        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
            $image_name = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/employees'), $image_name);
            $data['profile_image'] = $image_name;
        } else {
            unset($data['profile_image']);
        }

        // Record DPDP consent once (a recorded consent is never overwritten from this form)
        if (!$employee->dpdp_consent_given && $request->boolean('dpdp_consent_given')) {
            $data += $this->dpdpPayload($request);
        }

        DB::transaction(function () use ($employee, $data, $request) {
            $employee->update($data);
            $this->syncFamilyMembers($employee, (array) $request->input('family_members', []));
        });

        if (isset($data['status'])) {
            if ($data['status'] === 'Inactive' || $data['status'] === '0') {
                $employee->deactivate();
            } else {
                $employee->activate();
            }
        }

        // If employee is a supervisor, map any newly selected subordinate employees
        if ($isSupervisor && $request->filled('mapped_employee_ids')) {
            $mappedIds = array_filter((array) $request->input('mapped_employee_ids', []));
            if (!empty($mappedIds)) {
                Employee::whereIn('employee_id', $mappedIds)
                    ->where('employee_id', '!=', $employee_id)
                    ->update(['supervisor_id' => $employee_id]);
            }
        }

        return redirect()->route('employees.index')->with('success', 'Employee Updated Successfully');
    }

    /** At least two family members, each with name / phone / relation. */
    private function familyRules(): array
    {
        return [
            'family_members'            => 'required|array|min:2',
            'family_members.*.id'       => 'nullable|integer',
            'family_members.*.name'     => 'required|string|max:150',
            'family_members.*.phone'    => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'family_members.*.relation' => 'required|string|in:' . implode(',', EmployeeFamilyMember::RELATIONS),
        ];
    }

    /**
     * DPDP consent rules are applied only when consent is being recorded on edit.
     * The create form does not collect or require DPDP consent.
     */
    private function dpdpRules(bool $isCreate, bool $recordingNow = false): array
    {
        if ($isCreate || $recordingNow) {
            return [
                'dpdp_privacy_notice_ack' => 'accepted',
                'dpdp_consent_given'      => 'accepted',
                'dpdp_sharing_consent'    => 'nullable',
                'dpdp_consent_mode'       => 'required|in:Written,Electronic',
                'dpdp_consent_date'       => 'required|date|before_or_equal:today',
            ];
        }

        return [];
    }

    private function dpdpPayload(Request $request): array
    {
        $date = Carbon::parse($request->input('dpdp_consent_date', today()->toDateString()))
            ->setTimeFrom(now());

        return [
            'dpdp_privacy_notice_ack'  => 1,
            'dpdp_consent_given'       => 1,
            'dpdp_sharing_consent'     => $request->boolean('dpdp_sharing_consent') ? 1 : 0,
            'dpdp_consent_mode'        => $request->input('dpdp_consent_mode'),
            'dpdp_consent_at'          => $date,
            'dpdp_consent_version'     => 'DPDP-v1',
            'dpdp_consent_ip'          => $request->ip(),
            'dpdp_consent_recorded_by' => optional(auth()->user())->name ?? 'Admin',
        ];
    }

    /** Create / update / (soft) delete family rows so the employee ends up with exactly the submitted list. */
    private function syncFamilyMembers(Employee $employee, array $rows): void
    {
        $existing = $employee->familyMembers()->get()->keyBy('id');
        $keepIds = [];

        foreach ($rows as $row) {
            $attrs = [
                'name'     => trim($row['name'] ?? ''),
                'phone'    => trim($row['phone'] ?? ''),
                'relation' => $row['relation'] ?? '',
            ];

            $id = $row['id'] ?? null;
            if ($id && $existing->has((int) $id)) {
                $existing[(int) $id]->update($attrs);
                $keepIds[] = (int) $id;
            } else {
                $created = $employee->familyMembers()->create($attrs);
                $keepIds[] = $created->id;
            }
        }

        foreach ($existing as $id => $member) {
            if (!in_array($id, $keepIds, true)) {
                $member->delete(); // soft delete
            }
        }
    }

    private function extraMessages(): array
    {
        return [
            'family_members.required' => 'Details of at least two family members are required.',
            'family_members.min'      => 'Details of at least two family members are required.',
            'dpdp_privacy_notice_ack.accepted' => 'Please confirm that the Privacy Notice was shared with the employee.',
            'dpdp_consent_given.accepted'      => 'DPDP consent of the employee is required.',
        ];
    }

    private function extraAttributes(): array
    {
        return [
            'family_members.*.name'     => 'family member name',
            'family_members.*.phone'    => 'family member phone number',
            'family_members.*.relation' => 'family member relation',
            'notice_period_days'        => 'notice period',
            'address'                   => 'full address',
            'dpdp_consent_mode'         => 'consent mode',
            'dpdp_consent_date'         => 'consent date',
        ];
    }

    public function destroy($employee_id)
    {
        $employee = Employee::where('employee_id', $employee_id)->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found'
            ]);
        }

        $employee->deactivate();
        $employee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Employee deleted successfully'
        ]);
    }

    public function show(Request $request, $employee_id)
    {
        $employee = Employee::with(['country', 'state', 'city', 'designation', 'agency', 'shift', 'supervisorEmployee'])
            ->where('employee_id', $employee_id)
            ->firstOrFail();

        $cityName = $employee->city->name ?? (is_numeric($employee->posting_city) ? '' : $employee->posting_city);
        $stateName = $employee->state->name ?? (is_numeric($employee->posting_state) ? '' : $employee->posting_state);

        $countryName = $employee->country->name ?? '';
        if (empty($countryName) && $employee->state && $employee->state->country_id) {
            $country = Country::find($employee->state->country_id);
            $countryName = $country->name ?? '';
        }
        if (empty($countryName) && !empty($employee->posting_country) && !is_numeric($employee->posting_country)) {
            $countryName = $employee->posting_country;
        }
        if (empty($countryName) && (!empty($stateName) || !empty($cityName))) {
            $countryName = 'India';
        }

        $locationParts = array_filter([$cityName, $stateName, $countryName]);
        $fullLocation = !empty($locationParts) ? implode(', ', $locationParts) : '-';

        $shift = $employee->shift ?? Shift::first();
        $shiftName = $shift ? $shift->shift_name : 'General Shift';
        $shiftTiming = $shift ? date('h:i A', strtotime($shift->start_time)) . ' - ' . date('h:i A', strtotime($shift->end_time)) : '09:30 AM - 06:30 PM';
        $gracePeriod = ($shift->grace_period ?? 15) . ' mins';

        $supervisorEmp = $employee->supervisorEmployee;
        $subordinates = $employee->subordinates()->with(['designation', 'agency'])->whereNull('deleted_at')->get()->map(function ($sub) {
            return [
                'employee_id' => $sub->employee_id,
                'employee_name' => $sub->employee_name,
                'employee_email' => \App\Models\Employee::maskEmail($sub->employee_email),
                'designation_name' => $sub->designation?->name ?? 'Staff',
                'agency_name' => $sub->agency ? $sub->agency->agency_name : 'Direct / In-House',
                'status' => $sub->status,
                'profile_image' => $sub->profile_image,
            ];
        });

        $data = [
            'employee_id' => $employee->employee_id,
            'employee_name' => $employee->employee_name,
            'employee_email' => \App\Models\Employee::maskEmail($employee->employee_email),
            'employee_mobile' => \App\Models\Employee::maskMobile($employee->employee_mobile),
            'employee_gender' => $employee->employee_gender,
            'blood_group' => $employee->blood_group ?: '-',
            'joining_date' => $employee->joining_date,
            'date_of_confirmation' => $employee->date_of_confirmation,
            'employment_type' => $employee->employment_type,
            'designation_name' => $employee->designation->name ?? ($employee->designation->designation_name ?? 'Staff'),
            'agency_id' => $employee->agency_id,
            'agency_name' => $employee->agency ? $employee->agency->agency_name : 'Direct / In-House',
            'agency_code' => $employee->agency ? $employee->agency->agency_code : null,
            'has_agency' => !empty($employee->agency_id) && !empty($employee->agency),
            'shift_id' => $employee->shift_id,
            'shift_name' => $shiftName,
            'shift_timing' => $shiftTiming,
            'shift_display' => "{$shiftName} ({$shiftTiming})",
            'grace_period' => $gracePeriod,
            'is_supervisor' => (bool) $employee->is_supervisor,
            'supervisor_id' => $employee->supervisor_id,
            'supervisor_name' => $supervisorEmp ? $supervisorEmp->employee_name : '-',
            'supervisor_designation' => $supervisorEmp?->designation?->name ?? null,
            'subordinates_count' => $subordinates->count(),
            'subordinates' => $subordinates,
            'status' => $employee->status,
            'profile_image' => $employee->profile_image,
            'country_name' => $countryName ?: '-',
            'state_name' => $stateName ?: '-',
            'city_name' => $cityName ?: '-',
            'full_location' => $fullLocation,
        ];

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($data);
        }

        // salary structure is not linked to employees yet in Vyora (Payroll module comes later)
        $salaryStructure = null;

        $documents = \App\Models\EmployeeDocument::where('employee_id', $employee->employee_id)
            ->latest('id')
            ->get();

        return view('admin.employee.show', [
            'title' => ($employee->employee_name ?? 'Employee') . ' - Profile',
            'emp' => (object)$data,
            'employee' => $employee,
            'subordinates' => $subordinates,
            'salaryStructure' => $salaryStructure,
            'documents' => $documents,
        ]);
    }

    public function mapSubordinates(Request $request, $employee_id)
    {
        $supervisor = Employee::where('employee_id', $employee_id)->firstOrFail();

        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,employee_id',
        ]);

        Employee::whereIn('employee_id', $request->employee_ids)
            ->where('employee_id', '!=', $employee_id)
            ->update(['supervisor_id' => $employee_id]);

        return response()->json([
            'success' => true,
            'message' => count($request->employee_ids) . ' employee(s) mapped to ' . $supervisor->employee_name . ' successfully.'
        ]);
    }

    public function unmapSubordinate(Request $request, $employee_id)
    {
        $request->validate([
            'subordinate_id' => 'required|exists:employees,employee_id',
        ]);

        Employee::where('employee_id', $request->subordinate_id)
            ->where('supervisor_id', $employee_id)
            ->update(['supervisor_id' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Employee unmapped from supervisor successfully.'
        ]);
    }



    public function getStates($country_id)
    {
        return response()->json(
            CountryState::where('country_id', $country_id)->get()
        );
    }

    public function getCities($state_id)
    {
        return response()->json(
            StateCity::where('state_id', $state_id)->get()
        );
    }
}

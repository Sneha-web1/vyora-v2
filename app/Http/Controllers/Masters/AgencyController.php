<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Country;
use App\Models\CountryState;
use App\Models\StateCity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AgencyController extends Controller
{
    /**
     * Display a listing of the agencies.
     */
    public function index(Request $request)
    {
        $data['title'] = 'Agency Master';
        $data['titleRoute'] = 'Masters / Organisation / Agency';

        $totalAgencies = Agency::count();
        $activeAgencies = Agency::where('status', 'Active')->count();
        $inactiveAgencies = Agency::where('status', 'Inactive')->count();
        $mappedEmployees = Employee::whereNotNull('agency_id')->count();

        $data['totalAgencies'] = $totalAgencies;
        $data['activeAgencies'] = $activeAgencies;
        $data['inactiveAgencies'] = $inactiveAgencies;
        $data['mappedEmployees'] = $mappedEmployees;
        $data['selectedStatus'] = $request->status ?? '';
        $data['searchKeyword'] = $request->search ?? '';

        $query = Agency::withCount('employees');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('agency_name', 'like', "%{$search}%")
                  ->orWhere('agency_code', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%");
            });
        }

        if ($request->ajax()) {
            $agencies = $query->latest()->get();

            $formatted = $agencies->map(function ($agency, $idx) {
                return [
                    'index'          => $idx + 1,
                    'id'             => $agency->id,
                    'agency_name'    => $agency->agency_name,
                    'agency_code'    => $agency->agency_code ?: '-',
                    'contact_person' => $agency->contact_person ?: '-',
                    'email'          => $agency->email ?: '-',
                    'phone'          => $agency->phone ?: '-',
                    'city_state'     => trim(($agency->city ? $agency->city . ', ' : '') . ($agency->state ?: ''), ', ') ?: '-',
                    'status'         => $agency->status,
                    'employee_count' => $agency->employees_count,
                    'address'        => $agency->address ?: '',
                    'notes'          => $agency->notes ?: '',
                ];
            });

            return response()->json(['data' => $formatted]);
        }

        $data['agencies'] = $query->latest()->get();

        return view('masters.agency.index', $data);
    }

    /**
     * DataTables server-side list.
     */
    public function list(Request $request)
    {
        try {
            $search = $request->input('search')['value'] ?? null;
            $limit  = (int) $request->input('length', 10);
            $start  = (int) $request->input('start', 0);

            $totalRecords = Agency::count();

            $query = Agency::query()->withCount('employees');

            $status = $request->input('status');
            if (in_array($status, ['Active', 'Inactive'], true)) {
                $query->where('status', $status);
            }

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('agency_name', 'like', "%{$search}%")
                        ->orWhere('agency_code', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('state', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%");
                });
            }

            $filteredRecords = $query->count();

            $agencies = $query->orderBy('id', 'asc')
                ->skip($start)
                ->take($limit > 0 ? $limit : 100)
                ->get();

            $rows = [];
            foreach ($agencies as $index => $agency) {
                $location = implode(', ', array_filter([$agency->city, $agency->state, $agency->country]));

                $rows[] = [
                    'DT_RowIndex'    => $start + $index + 1,
                    'agency_name'    => e($agency->agency_name),
                    'agency_code'    => e($agency->agency_code ?: '--'),
                    'contact_person' => e($agency->contact_person ?: '--'),
                    'email'          => e($agency->email ?: '--'),
                    'phone'          => e($agency->phone ?: '--'),
                    'location'       => e($location ?: '--'),
                    'employees'      => '<a href="' . route('masters.organisation.agency.mapping', ['filter_agency' => $agency->id]) . '" title="View Mapped Employees">' . (int) $agency->employees_count . '</a>',
                    'status'         => $agency->status === 'Active'
                        ? '<a href="javascript:void(0);" onclick="openStatusModal(' . $agency->id . ', \'Active\')" title="Click to make Inactive"><span class="badge bg-success">Active</span></a>'
                        : '<a href="javascript:void(0);" onclick="openStatusModal(' . $agency->id . ', \'Inactive\')" title="Click to make Active"><span class="badge bg-danger">Inactive</span></a>',
                    'action'         => '<button onclick="viewAgency(' . $agency->id . ')" class="btn" title="View"><i class="ti ti-eye"></i></button>
                <a href="' . route('masters.organisation.agency.edit', $agency->id) . '" class="btn" title="Edit"><i class="ti ti-edit"></i></a>
                <button onclick="deleteAgency(' . $agency->id . ')" class="btn" title="Delete"><i class="ti ti-trash"></i></button>',
                ];
            }

            return response()->json([
                'draw'            => intval($request->input('draw')),
                'recordsTotal'    => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data'            => $rows,
            ]);
        } catch (\Exception $e) {
            Log::error('Agency List Error: ' . $e->getMessage());

            return response()->json([
                'error'   => true,
                'message' => 'Something went wrong while fetching agencies: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fill country/state/city name columns from the master IDs
     * (keeps list, search and view working off the same masters).
     */
    private function withLocationNames(array $data): array
    {
        $country = !empty($data['country_id']) ? Country::find($data['country_id']) : null;
        $state   = !empty($data['state_id']) ? CountryState::find($data['state_id']) : null;
        $city    = !empty($data['city_id']) ? StateCity::find($data['city_id']) : null;

        // Keep the hierarchy consistent
        if ($state && $country && (int) $state->country_id !== (int) $country->id) {
            $state = null;
            $city = null;
        }
        if ($city && $state && (int) $city->state_id !== (int) $state->id) {
            $city = null;
        }

        $data['country_id'] = $country?->id;
        $data['country']    = $country?->name;
        $data['state_id']   = $state?->id;
        $data['state']      = $state?->name;
        $data['city_id']    = $city?->id;
        $data['city']       = $city?->name;

        return $data;
    }

    /**
     * Add Agency page.
     */
    public function create()
    {
        $data['title'] = 'Add Agency';
        $data['titleRoute'] = 'Masters / Organisation / Agency / Add';
        $data['agency'] = null;
        $data['countries'] = Country::orderBy('name')->get(['id', 'name']);
        $data['states'] = collect();
        $data['cities'] = collect();

        return view('masters.agency.form', $data);
    }

    /**
     * Edit Agency page.
     */
    public function edit($id)
    {
        $agency = Agency::findOrFail($id);

        $data['title'] = 'Edit Agency';
        $data['titleRoute'] = 'Masters / Organisation / Agency / Edit';
        $data['agency'] = $agency;
        $data['countries'] = Country::orderBy('name')->get(['id', 'name']);
        $data['states'] = $agency->country_id
            ? CountryState::where('country_id', $agency->country_id)->orderBy('name')->get(['id', 'name'])
            : collect();
        $data['cities'] = $agency->state_id
            ? StateCity::where('state_id', $agency->state_id)->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('masters.agency.form', $data);
    }

    /**
     * Store a newly created agency.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'agency_name'    => 'required|string|max:255',
            'agency_code'    => ['nullable', 'string', 'max:50', Rule::unique('agencies', 'agency_code')->whereNull('deleted_at')],
            'contact_person' => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:50',
            'address'        => 'nullable|string',
            'country_id'     => 'nullable|integer|exists:countries,id',
            'state_id'       => 'nullable|integer|exists:country_states,id',
            'city_id'        => 'nullable|integer|exists:state_cities,id',
            'status'         => 'required|in:Active,Inactive',
            'notes'          => 'nullable|string',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $agency = Agency::create($this->withLocationNames($validator->validated()));

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Agency created successfully!',
                'data'    => $agency,
            ]);
        }

        return redirect()->route('masters.organisation.agency')->with('success', 'Agency created successfully!');
    }

    /**
     * Display details of a specific agency (for modals).
     */
    public function show($id)
    {
        $agency = Agency::withCount('employees')->findOrFail($id);

        return response()->json([
            'success' => true,
            'agency'  => $agency,
            'meta'    => [
                'created_at' => $agency->created_at ? Carbon::parse($agency->created_at)->format('d M Y, h:i A') : '--',
                'updated_at' => $agency->updated_at ? Carbon::parse($agency->updated_at)->format('d M Y, h:i A') : '--',
            ],
        ]);
    }

    /**
     * Update an agency.
     */
    public function update(Request $request, $id)
    {
        $agency = Agency::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'agency_name'    => 'required|string|max:255',
            'agency_code'    => ['nullable', 'string', 'max:50', Rule::unique('agencies', 'agency_code')->ignore($id)->whereNull('deleted_at')],
            'contact_person' => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:50',
            'address'        => 'nullable|string',
            'country_id'     => 'nullable|integer|exists:countries,id',
            'state_id'       => 'nullable|integer|exists:country_states,id',
            'city_id'        => 'nullable|integer|exists:state_cities,id',
            'status'         => 'required|in:Active,Inactive',
            'notes'          => 'nullable|string',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors'  => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $agency->update($this->withLocationNames($validator->validated()));

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Agency updated successfully!',
                'data'    => $agency,
            ]);
        }

        return redirect()->route('masters.organisation.agency')->with('success', 'Agency updated successfully!');
    }

    /**
     * Toggle status of an agency.
     */
    public function updateStatus(Request $request, $id)
    {
        $agency = Agency::findOrFail($id);
        $agency->status = $request->status == '1' || $request->status === 'Active' ? 'Active' : 'Inactive';
        $agency->save();

        return response()->json([
            'success' => true,
            'message' => 'Agency status updated successfully!'
        ]);
    }

    /**
     * Remove the specified agency.
     */
    public function destroy($id)
    {
        $agency = Agency::findOrFail($id);

        // Reset agency_id for mapped employees so they aren't orphaned
        Employee::where('agency_id', $id)->update(['agency_id' => null]);

        $agency->delete();

        return response()->json([
            'success' => true,
            'message' => 'Agency deleted successfully!'
        ]);
    }

    /**
     * Show Employee-Agency Mapping screen.
     */
    public function mapping(Request $request)
    {
        $data['title'] = 'Employee Mapping with Agency';
        $data['titleRoute'] = 'Masters / Organisation / Agency / Employee Mapping';
        $data['designationNames'] = Designation::pluck('name', 'id');
        $data['agencies'] = Agency::orderBy('agency_name')->get();

        $query = Employee::with(['agency']);

        // Filter by Agency
        if ($request->filled('filter_agency')) {
            if ($request->filter_agency === 'unassigned') {
                $query->whereNull('agency_id');
            } else {
                $query->where('agency_id', $request->filter_agency);
            }
        }

        // Filter by search term
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('employee_name', 'like', "%{$search}%")
                    ->orWhere('employee_email', 'like', "%{$search}%")
                    ->orWhere('employee_mobile', 'like', "%{$search}%");
            });
        }

        $data['employees'] = $query->orderBy('employee_name')->get();
        $data['totalCount'] = Employee::count();
        $data['mappedCount'] = Employee::whereNotNull('agency_id')->count();
        $data['unmappedCount'] = Employee::whereNull('agency_id')->count();

        return view('masters.agency.mapping', $data);
    }

    /**
     * Save bulk Employee-Agency Mapping.
     */
    public function saveMapping(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,employee_id',
            'target_agency_id' => 'nullable', // null means unassign
        ]);

        $agencyId = $request->target_agency_id;
        if ($agencyId !== null && $agencyId !== '' && $agencyId !== 'unassign') {
            $agency = Agency::findOrFail($agencyId);
            $targetId = $agency->id;
            $msg = count($request->employee_ids) . " employee(s) mapped to {$agency->agency_name} successfully!";
        } else {
            $targetId = null;
            $msg = count($request->employee_ids) . " employee(s) unassigned from agencies successfully!";
        }

        Employee::whereIn('employee_id', $request->employee_ids)
            ->update(['agency_id' => $targetId]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }
}
<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DesignationController extends Controller
{
    public function index()
    {
        $data['title'] = 'Designation List';
        $data['titleRoute'] = 'Masters / Organisation / Designation';
        return view('home.designation.index', $data);
    }

    /**
     * DataTable AJAX list
     */
    public function list(Request $request)
    {
        try {
            $search = $request->input('search')['value'] ?? null;
            $limit = (int) $request->input('length', 10);
            $start = (int) $request->input('start', 0);

            $query = Designation::with(['company', 'branch', 'category', 'department']);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhereHas('company', fn ($c) => $c->where('company_name', 'like', "%{$search}%"))
                        ->orWhereHas('branch', fn ($b) => $b->where('branch_name', 'like', "%{$search}%"))
                        ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('department', fn ($d) => $d->where('department_name', 'like', "%{$search}%"));
                });
            }

            $totalRecord = $query->count();

            // Sorting (column header par click)
            $dir = ($request->input('order')[0]['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
            $colIdx = $request->input('order')[0]['column'] ?? 0;
            $colKey = $request->input("columns.{$colIdx}.data");

            $subSorts = [
                'company_name' => Company::select('company_name')->whereColumn('companies.id', 'designations.company_id'),
                'branch_name' => Branch::select('branch_name')->whereColumn('branches.id', 'designations.branch_id'),
                'category_name' => Category::select('name')->whereColumn('categories.id', 'designations.category_id'),
                'department_name' => Department::select('department_name')->whereColumn('departments.id', 'designations.department_id'),
            ];
            $directSorts = [
                'designation_name' => 'name',
                'designation_code' => 'code',
                'kpi_weightage' => 'kpi_weightage',
                'competency_weight' => 'competency_weight',
                'status' => 'status',
            ];

            if (isset($subSorts[$colKey])) {
                $query->orderBy($subSorts[$colKey], $dir);
            } elseif (isset($directSorts[$colKey])) {
                $query->orderBy($directSorts[$colKey], $dir);
            } else {
                $query->orderBy('id', $dir);
            }

            $designations = $query->skip($start)->take($limit > 0 ? $limit : 10)->get();

            $rows = [];
            foreach ($designations as $index => $desig) {
                $isActive = in_array($desig->status, ['Active', 1, '1'], true);
                $rows[] = [
                    'id' => $desig->id,
                    'DT_RowIndex' => $start + $index + 1,
                    'company_name' => $desig->company->company_name ?? '--',
                    'branch_name' => $desig->branch->branch_name ?? '--',
                    'category_name' => $desig->category->name ?? '--',
                    'department_name' => $desig->department->department_name ?? '--',
                    'designation_name' => $desig->name ?? '--',
                    'designation_code' => $desig->code ?? '--',
                    'kpi_weightage' => $desig->kpi_weightage ?? '0',
                    'competency_weight' => $desig->competency_weight ?? '0',
                    'status' => $isActive
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>',
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecord,
                'recordsFiltered' => $totalRecord,
                'data' => $rows,
            ]);
        } catch (\Exception $e) {
            \Log::error('Designation List Error: ' . $e->getMessage());

            return response()->json([
                'error' => true,
                'message' => 'Something went wrong while fetching designation records.',
                'exception' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Designation details (JSON)
     */
    public function show($id)
    {
        $d = Designation::with(['company', 'branch', 'category', 'department'])->findOrFail($id);

        return response()->json([
            'id' => $d->id,
            'company' => $d->company->company_name ?? null,
            'branch' => $d->branch->branch_name ?? null,
            'category' => $d->category->name ?? null,
            'department' => $d->department->department_name ?? null,
            'name' => $d->name,
            'code' => $d->code,
            'kpi_weightage' => $d->kpi_weightage,
            'competency_weight' => $d->competency_weight,
            'status' => $d->status,
            'edit_url' => route('masters.organisation.designation.edit', $d->id),
        ]);
    }

    public function create()
    {
        $data['title'] = 'Master / Organisation / Designation Create';
        $data['companies'] = Company::orderBy('company_name')->get();
        $data['categories'] = Category::orderBy('name')->get();
        return view('home.designation.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate($this->rules($request));

        Designation::create($this->payload($request));

        return redirect()->route('masters.organisation.designation')
            ->with('success', 'Designation created successfully!');
    }

    public function edit(string $id)
    {
        $designation = Designation::findOrFail($id);

        $data['title'] = 'Master / Organisation / Designation Edit';
        $data['designation'] = $designation;
        $data['companies'] = Company::orderBy('company_name')->get();
        $data['categories'] = Category::orderBy('name')->get();
        $data['branches'] = $designation->company_id
            ? Branch::where('company_id', $designation->company_id)->orderBy('branch_name')->get()
            : collect();
        $data['departments'] = $designation->company_id
            ? Department::where('company_id', $designation->company_id)
                ->when($designation->branch_id, fn ($q) => $q->where('branch_id', $designation->branch_id))
                ->orderBy('department_name')->get()
            : collect();

        return view('home.designation.edit', $data);
    }

    public function update(Request $request, string $id)
    {
        $designation = Designation::findOrFail($id);

        $request->validate($this->rules($request, $designation->id));

        $designation->update($this->payload($request));

        return redirect()->route('masters.organisation.designation')
            ->with('success', 'Designation updated successfully!');
    }

    public function destroy(string $id)
    {
        $designation = Designation::find($id);

        if (!$designation) {
            return response()->json(['success' => false, 'message' => 'Designation not found.'], 404);
        }

        try {
            $staffCount = Employee::where('designation_id', $id)->whereNull('deleted_at')->count();
            if ($staffCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete designation: {$staffCount} employee(s) are assigned to it.",
                ], 422);
            }

            $designation->delete();
            return response()->json(['success' => true, 'message' => 'Designation deleted successfully!']);
        } catch (\Exception $e) {
            \Log::error('Designation Delete Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Something went wrong while deleting.'], 500);
        }
    }

    /**
     * Company mandatory; Branch, Category, Department optional.
     */
    private function rules(Request $request, $ignoreId = null): array
    {
        $deptRule = Rule::exists('departments', 'id')->where('company_id', $request->company_id);
        if ($request->filled('branch_id')) {
            $deptRule = $deptRule->where('branch_id', $request->branch_id);
        }

        return [
            'company_id' => 'required|exists:companies,id',
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $request->company_id)],
            'category_id' => 'nullable|exists:categories,id',
            'department_id' => ['nullable', $deptRule],
            'name' => 'required|string|max:255',
            'designation_code' => ['required', 'string', 'max:50', Rule::unique('designations', 'code')->ignore($ignoreId)],
            'kpi_weightage' => 'required|numeric|min:0|max:100',
            'competency_weight' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:Active,Inactive',
        ];
    }

    private function payload(Request $request): array
    {
        return [
            'company_id' => $request->company_id,
            'branch_id' => $request->branch_id,
            'category_id' => $request->category_id,
            'department_id' => $request->department_id,
            'name' => $request->name,
            'code' => $request->designation_code,
            'kpi_weightage' => $request->kpi_weightage,
            'competency_weight' => $request->competency_weight,
            'status' => $request->status,
        ];
    }
}
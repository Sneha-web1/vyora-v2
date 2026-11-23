<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Policy;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PolicyController extends Controller
{
    public function index()
    {
        $data['title'] = 'Policy List';
        $data['titleRoute']='Masters / Organisation / Policy';
        return view('home.policy.index', $data);
    }

    public function list(Request $request)
    {
        $search = $request->input('search')['value'] ?? null;
        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);
        $query = Policy::with(['company', 'branch', 'department']);

        if ($search) {
            $query->where(function ($w) use ($search) {
                $w->where('policy_title', 'like', "%{$search}%")
                    ->orWhere('policy_code', 'like', "%{$search}%")
                    ->orWhereHas('company', function ($q) use ($search) {
                        $q->where('company_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('department', function ($q) use ($search) {
                        $q->where('department_name', 'like', "%{$search}%");
                    });
            });
        }

        $totalRecord = $query->count();
        $policies = $query->orderBy('id', 'asc')->skip($start)->take($limit)->get();

        $rows = [];
        foreach ($policies as $index => $policy) {
            $rows[] = [
                'id' => $policy->id,
                'DT_RowIndex' => $start + $index + 1,
                'title' => $policy->policy_title,
                'code' => $policy->policy_code,
                'company' => $policy->company->company_name ?? '--',
                'branch' => $policy->branch->branch_name ?? '--',
                'department' => $policy->department->department_name ?? '--',
                'effective_from' => $this->formatDate($policy->effective_from),
                'expiry_date' => $this->formatDate($policy->expiry_date),
                'status' => $policy->status === 'Active'
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
    }

    public function show($id)
    {
        $policy = Policy::with(['company', 'branch', 'department'])->findOrFail($id);

        return response()->json([
            'id' => $policy->id,
            'title' => $policy->policy_title,
            'code' => $policy->policy_code,
            'company' => $policy->company->company_name ?? null,
            'branch' => $policy->branch->branch_name ?? null,
            'department' => $policy->department->department_name ?? null,
            'effective_from' => $this->formatDate($policy->effective_from),
            'expiry_date' => $this->formatDate($policy->expiry_date),
            'status' => $policy->status,
            'description' => $policy->description,
            'document_url' => $policy->policy_document ? asset($policy->policy_document) : null,
            'created_at' => $this->formatDate($policy->created_at, 'd M Y, h:i A'),
            'updated_at' => $this->formatDate($policy->updated_at, 'd M Y, h:i A'),
            'edit_url' => route('masters.organisation.policy.edit', $policy->id),
        ]);
    }

    private function departmentRule(Request $request)
    {
        $rule = Rule::exists('departments', 'id')->where('company_id', $request->company_id);
        if ($request->filled('branch_id')) {
            $rule = $rule->where('branch_id', $request->branch_id);
        }
        return $rule;
    }

    private function formatDate($value, string $format = 'd M, Y'): string
    {
        if (empty($value)) {
            return '--';
        }
        try {
            return \Carbon\Carbon::parse($value)->format($format);
        } catch (\Exception $e) {
            return (string) $value;
        }
    }

    public function create()
    {
        $data['title'] = 'Master / Organisation / Policy Create';
        $data['companies'] = Company::orderBy('company_name')->get();
        return view('home.policy.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'policy_title' => 'required|string|max:255',
            'policy_code' => 'required|string|max:50|unique:policies,policy_code',
            'company_id' => 'required|exists:companies,id',
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $request->company_id)],
            'department_id' => ['nullable', $this->departmentRule($request)],
            'effective_from' => 'required|date',
            'status' => 'required|in:Active,Inactive',
            'policy_file' => 'nullable|mimes:pdf|max:2048',
        ]);

        $data = $request->only(
            'policy_title',
            'policy_code',
            'company_id',
            'branch_id',
            'department_id',
            'effective_from',
            'expiry_date',
            'status',
            'description'
        );
        $data['description'] = strip_tags($data['description'] ?? '');
        if ($request->hasFile('policy_file')) {
            $file = $request->file('policy_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('policy_documents'), $filename);
            $data['policy_document'] = 'policy_documents/' . $filename;
        }
        Policy::create($data);

        return redirect()->route('masters.organisation.policy')
            ->with('success', 'Policy created successfully!');
    }

    public function edit($id)
    {
        $data['title'] = 'Master / Organisation / Policy Edit';
        $data['policy'] = Policy::findOrFail($id);
        $data['companies'] = Company::orderBy('company_name')->get();
        $data['branches'] = $data['policy']->company_id
            ? Branch::where('company_id', $data['policy']->company_id)->orderBy('branch_name')->get()
            : collect();
        $data['departments'] = $data['policy']->company_id
            ? Department::where('company_id', $data['policy']->company_id)
                ->when($data['policy']->branch_id, fn ($q) => $q->where('branch_id', $data['policy']->branch_id))
                ->orderBy('department_name')->get()
            : collect();
        return view('home.policy.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $policy = Policy::findOrFail($id);

        $request->validate([
            'policy_title' => 'required|string|max:255',
            'policy_code' => 'required|string|max:50|unique:policies,policy_code,' . $policy->id,
            'company_id' => 'required|exists:companies,id',
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $request->company_id)],
            'department_id' => ['nullable', $this->departmentRule($request)],
            'effective_from' => 'required|date',
            'status' => 'required|in:Active,Inactive',
            'policy_file' => 'nullable|mimes:pdf|max:2048',
        ]);

        $data = $request->only(
            'policy_title',
            'policy_code',
            'company_id',
            'branch_id',
            'department_id',
            'effective_from',
            'expiry_date',
            'status',
            'description'
        );
        $data['description'] = strip_tags($data['description'] ?? '');
        if ($request->hasFile('policy_file')) {
            if ($policy->policy_document && file_exists(public_path($policy->policy_document))) {
                unlink(public_path($policy->policy_document));
            }
            $file = $request->file('policy_file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('policy_documents'), $filename);
            $data['policy_document'] = 'policy_documents/' . $filename;
        }

        $policy->update($data);

        return redirect()->route('masters.organisation.policy')
            ->with('success', 'Policy updated successfully!');
    }

    public function destroy($id)
    {
        $policy = Policy::find($id);
        if (!$policy) {
            return response()->json(['success' => false, 'message' => 'Policy not found.']);
        }
        $policy->delete();
        return response()->json(['success' => true, 'message' => 'Policy deleted successfully!']);
    }
}
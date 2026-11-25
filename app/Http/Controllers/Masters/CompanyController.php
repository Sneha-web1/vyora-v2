<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    public function index()
    {
        $data['title'] = 'Organization List';
        $data['companyExists'] = Company::exists();
        $data['titleRoute'] = 'Masters / Organization';
        $data['imageUrl'] = "https://picsum.photos/200/200?random=" . rand(1, 1000);
        return view('home.company.index', $data);
    }

    public function create()
    {
        if (Company::exists()) {
            return redirect()->route('masters.organisation.company')
                ->with('error', 'Only one organization can be added.');
        }

        $data['country'] = Country::all();
        $data['title'] = 'Create Organization';
        $data['imageUrl'] = "https://picsum.photos/200/200?random=" . rand(1, 1000);
        return view('home.company.create', $data);
    }

    public function store(Request $request)
    {
        if (Company::exists()) {
            return redirect()->route('masters.organisation.company')
                ->with('error', 'Only one organization can be added.');
        }

        $validatedData = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_code' => 'nullable|string|max:255|unique:companies,company_code',
            'contact_no' => 'required|string|max:20',
            'landline_no' => 'nullable|string|max:20',
            'email' => 'required|email|unique:companies,email',
            'gst_no' => 'nullable|string|max:50',
            'pan_no' => 'nullable|string|max:50',
            'tan_no' => 'nullable|string|max:50',
            'cin_no' => 'nullable|string|max:50',
            'iec' => 'nullable|string|max:50',
            'duns_no' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'company_logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'address' => 'required|string',
            'country' => 'required|exists:countries,id',
            'state' => 'required',
            'city' => 'required',
            'pincode' => 'required|string|max:10',
            'status' => 'required|in:Active,Inactive',
        ], [
            'company_logo.image' => 'Organization logo must be an image file.',
            'company_logo.mimes' => 'Organization logo must be a PNG, JPG or JPEG file.',
            'company_logo.max' => 'Organization logo must not be larger than 2 MB.',
            'company_logo.uploaded' => 'Organization logo must not be larger than 2 MB.',
        ]);

        $company = new Company();
        $company->company_name = Str::title($validatedData['company_name']);
        $company->company_code = $validatedData['company_code'] ?? null;
        $company->contact_no = $validatedData['contact_no'];
        $company->landline_no = $validatedData['landline_no'] ?? null;
        $company->email = $validatedData['email'];
        $company->gst_no = $validatedData['gst_no'] ?? null;
        $company->pan_no = $validatedData['pan_no'] ?? null;
        $company->tan_no = $validatedData['tan_no'] ?? null;
        $company->cin_no = $validatedData['cin_no'] ?? null;
        $company->iec = $validatedData['iec'] ?? null;
        $company->duns_no = $validatedData['duns_no'] ?? null;
        $company->website = $validatedData['website'] ?? null;
        $company->address = $validatedData['address'];
        $company->country = $validatedData['country'];
        $company->state = $validatedData['state'];
        $company->city = $validatedData['city'];
        $company->pincode = $validatedData['pincode'];
        $company->status = $validatedData['status'];

        if ($request->hasFile('company_logo')) {
            $file = $request->file('company_logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/company'), $filename);
            $company->company_logo = $filename;
        }

        $company->save();

        return redirect()->route('masters.organisation.company')->with('success', 'Company created successfully!');
    }

    public function list(Request $request)
    {
        $search = $request->input('search')['value'] ?? null;
        $limit = $request->input('length', 10);
        $start = $request->input('start', 0);

        $query = Company::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('company_code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $totalRecord = $query->count();
        $companies = $query->skip($start)->take($limit)->get();

        $rows = [];
        foreach ($companies as $index => $company) {
            $logoUrl = $company->company_logo
                ? asset('uploads/company/' . $company->company_logo)
                : asset('default-logo.png');

            $rows[] = [
                'DT_RowIndex' => $start + $index + 1,
                'logo' => '<img src="' . $logoUrl . '" alt="Logo" style="width:50px; height:auto;">',
                'company_name' => $company->company_name ?? '--',
                'company_code' => $company->company_code ?? '--',
                'contact_no' => $company->contact_no ?? '--',
                'email' => $company->email ?? '--',
                'status' => $company->status == 'Active'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>',
                'action' => '<button onclick="viewCompany(' . $company->id . ')" class="btn" title="View"><i class="ti ti-eye"></i></button>
                <a href="' . route('masters.organisation.company.edit', $company->id) . '" title="Edit"><i class="ti ti-edit"></i></a>
                <button onclick="deleteCompany(' . $company->id . ')" class="btn"><i class="ti ti-trash"></i></button>',
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
        $company = Company::with(['countryDetail', 'stateDetail', 'cityDetail'])->findOrFail($id);

        return response()->json([
            'id' => $company->id,
            'logo' => $company->company_logo
                ? asset('uploads/company/' . $company->company_logo)
                : asset('default-logo.png'),
            'company_name' => $company->company_name,
            'company_code' => $company->company_code,
            'contact_no' => $company->contact_no,
            'landline_no' => $company->landline_no,
            'email' => $company->email,
            'website' => $company->website,
            'gst_no' => $company->gst_no,
            'pan_no' => $company->pan_no,
            'tan_no' => $company->tan_no,
            'cin_no' => $company->cin_no,
            'iec' => $company->iec,
            'duns_no' => $company->duns_no,
            'address' => $company->address,
            'country' => optional($company->countryDetail)->name,
            'state' => optional($company->stateDetail)->name,
            'city' => optional($company->cityDetail)->name,
            'pincode' => $company->pincode,
            'status' => $company->status,
            'created_at' => optional($company->created_at)->format('d M Y, h:i A'),
            'updated_at' => optional($company->updated_at)->format('d M Y, h:i A'),
            'edit_url' => route('masters.organisation.company.edit', $company->id),
        ]);
    }

    public function edit($id)
    {
        $data['company'] = Company::findOrFail($id);
        $data['country'] = Country::all();
        $data['title'] = 'Edit Organization';
        return view('home.company.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);

        $validatedData = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_code' => 'nullable|string|max:255|unique:companies,company_code,' . $company->id,
            'contact_no' => 'required|string|max:20',
            'landline_no' => 'nullable|string|max:20',
            'email' => 'required|email|unique:companies,email,' . $company->id,
            'gst_no' => 'nullable|string|max:50',
            'pan_no' => 'nullable|string|max:50',
            'tan_no' => 'nullable|string|max:50',
            'cin_no' => 'nullable|string|max:50',
            'iec' => 'nullable|string|max:50',
            'duns_no' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'company_logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'address' => 'required|string',
            'country' => 'required|exists:countries,id',
            'state' => 'required',
            'city' => 'required',
            'pincode' => 'required|string|max:10',
            'status' => 'required|in:Active,Inactive',
        ], [
            'company_logo.image' => 'Organization logo must be an image file.',
            'company_logo.mimes' => 'Organization logo must be a PNG, JPG or JPEG file.',
            'company_logo.max' => 'Organization logo must not be larger than 2 MB.',
            'company_logo.uploaded' => 'Organization logo must not be larger than 2 MB.',
        ]);

        $company->company_name = Str::title($validatedData['company_name']);
        $company->company_code = $validatedData['company_code'] ?? null;
        $company->contact_no = $validatedData['contact_no'];
        $company->landline_no = $validatedData['landline_no'] ?? null;
        $company->email = $validatedData['email'];
        $company->gst_no = $validatedData['gst_no'] ?? null;
        $company->pan_no = $validatedData['pan_no'] ?? null;
        $company->tan_no = $validatedData['tan_no'] ?? null;
        $company->cin_no = $validatedData['cin_no'] ?? null;
        $company->iec = $validatedData['iec'] ?? null;
        $company->duns_no = $validatedData['duns_no'] ?? null;
        $company->website = $validatedData['website'] ?? null;
        $company->address = $validatedData['address'];
        $company->country = $validatedData['country'];
        $company->state = $validatedData['state'];
        $company->city = $validatedData['city'];
        $company->pincode = $validatedData['pincode'];
        $company->status = $validatedData['status'];

        if ($request->hasFile('company_logo')) {
            if ($company->company_logo && file_exists(public_path('uploads/company/' . $company->company_logo))) {
                unlink(public_path('uploads/company/' . $company->company_logo));
            }
            $file = $request->file('company_logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/company'), $filename);
            $company->company_logo = $filename;
        }

        $company->save();
        return redirect()->route('masters.organisation.company')->with('success', 'Company updated successfully!');
    }

    public function destroy($id)
    {
        $company = Company::findOrFail($id);
        $company->delete(); // soft delete (logo file is kept so the record can be restored)

        return response()->json(['success' => true, 'message' => 'Organization deleted successfully']);
    }
}

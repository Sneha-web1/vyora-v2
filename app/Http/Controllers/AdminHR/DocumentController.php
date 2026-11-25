<?php

namespace App\Http\Controllers\AdminHR;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class DocumentController extends Controller
{
    /**
     * Standard Document Types supported across the HRMS
     */
    public const DOCUMENT_TYPES = [
        'Aadhaar Card',
        'PAN Card',
        'Passport',
        'Voter ID',
        'Driving License',
        'Educational Degree',
        'Experience / Relieving Letter',
        'Salary Slip (Previous)',
        'Bank Passbook / Cheque',
        'Offer / Appointment Letter',
        'Resume / CV',
        'Other',
    ];

    /**
     * Display a listing of employee documents in the Document Vault.
     */
    public function index(Request $request)
    {
        $data['title'] = 'Employee Document Vault & Verification';

        // Handle AJAX DataTables query (server-side, same contract as Company / Branch lists)
        if ($request->ajax()) {
            $query = EmployeeDocument::with(['employee.designation']);

            // Filter by employee
            if ($request->filled('employee_id') && $request->employee_id !== 'all') {
                $query->where('employee_id', $request->employee_id);
            }

            // Filter by employee status (Default: 'active')
            $staffStatus = $request->input('employee_status', 'active');
            if ($staffStatus === 'active' && (!$request->filled('employee_id') || $request->employee_id === 'all')) {
                $query->whereHas('employee', function ($q) {
                    $q->active();
                });
            } elseif ($staffStatus === 'inactive' && (!$request->filled('employee_id') || $request->employee_id === 'all')) {
                $query->whereHas('employee', function ($q) {
                    $q->where(function ($sub) {
                        $sub->where('status', 'Inactive')
                            ->orWhere('status', '0')
                            ->orWhere('status', 0)
                            ->orWhere('status', 'inactive')
                            ->orWhereNotNull('deleted_at');
                    });
                });
            }

            // Filter by document type
            if ($request->filled('document_type') && $request->document_type !== 'all') {
                $query->where('document_type', $request->document_type);
            }

            // Summary counters for the KPI cards (ignore the verification-status filter)
            if ($request->boolean('kpi')) {
                return response()->json([
                    'total'    => (clone $query)->count(),
                    'approved' => (clone $query)->where('verification_status', 'Approved')->count(),
                    'pending'  => (clone $query)->where('verification_status', 'Pending')->count(),
                    'rejected' => (clone $query)->where('verification_status', 'Rejected')->count(),
                ]);
            }

            // Filter by verification status
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('verification_status', $request->status);
            }

            // DataTables keyword search
            $search = $request->input('search')['value'] ?? null;
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('document_name', 'like', "%{$search}%")
                        ->orWhere('document_number', 'like', "%{$search}%")
                        ->orWhere('document_type', 'like', "%{$search}%")
                        ->orWhereHas('employee', function ($eq) use ($search) {
                            $eq->where('employee_name', 'like', "%{$search}%")
                                ->orWhereEmployeeContact($search);
                        });
                });
            }

            $limit = (int) $request->input('length', 10);
            $start = (int) $request->input('start', 0);

            $totalRecord = $query->count();
            $docs = $query->latest('id')->skip($start)->take($limit)->get();

            $rows = [];
            foreach ($docs as $index => $doc) {
                $emp = $doc->employee;
                $empName = $emp ? $emp->employee_name : 'Unknown Staff';
                $empCode = $emp ? '#EMP-' . $emp->employee_id : '-';
                $designation = $emp && $emp->designation ? ($emp->designation->name ?? $emp->designation->designation_name) : 'Staff';

                $validity = '<span class="text-muted">No Expiry</span>';
                if ($doc->issue_date || $doc->expiry_date) {
                    $validity = '';
                    if ($doc->issue_date) {
                        $validity .= '<div class="text-muted fs-12">Issued: ' . $doc->issue_date->format('d M, Y') . '</div>';
                    }
                    if ($doc->expiry_date) {
                        $expired = $doc->expiry_date->isPast();
                        $validity .= '<div class="fs-12 ' . ($expired ? 'text-danger fw-bold' : 'text-dark') . '">Expires: '
                            . $doc->expiry_date->format('d M, Y') . '</div>';
                    }
                }

                $statusBadge = match ($doc->verification_status) {
                    'Approved' => '<span class="badge bg-success">Approved</span>',
                    'Rejected' => '<span class="badge bg-danger" title="' . e($doc->rejection_reason) . '">Rejected</span>',
                    default    => '<span class="badge bg-warning text-dark">Pending</span>',
                };

                $rows[] = [
                    'DT_RowIndex' => $start + $index + 1,
                    'employee'    => '<div><span class="fw-medium">' . e($empName) . '</span>'
                        . '<div class="text-muted fs-12">' . e($empCode) . ' &bull; ' . e($designation) . '</div></div>',
                    'document'    => '<div><span class="fw-medium">' . e($doc->document_name) . '</span>'
                        . '<div class="text-muted fs-12">' . e($doc->document_type)
                        . ($doc->document_number ? ' &bull; ' . e($doc->document_number) : '') . '</div></div>',
                    'file'        => '<div class="d-flex align-items-center gap-2"><span class="badge bg-light text-dark border">'
                        . e(strtoupper($doc->file_extension)) . '</span><div><div class="text-truncate" style="max-width:160px;" title="'
                        . e($doc->file_name) . '">' . e($doc->file_name) . '</div><div class="text-muted fs-12">'
                        . e($doc->formatted_file_size) . '</div></div></div>',
                    'validity'    => $validity,
                    'status'      => $statusBadge,
                    'action'      => '<div class="action-icon d-inline-flex">'
                        . '<a href="javascript:void(0);" onclick="viewDocumentDetails(' . $doc->id . ')" class="me-2" title="View"><i class="ti ti-eye"></i></a>'
                        . '<a href="' . route('employees.documents.download', $doc->id) . '" class="me-2" title="Download"><i class="ti ti-download"></i></a>'
                        . '<a href="javascript:void(0);" class="btn-delete-doc" data-id="' . $doc->id . '" data-name="' . e($doc->document_name) . '" title="Delete"><i class="ti ti-trash"></i></a>'
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

        // Summary KPI Metrics for Active Employees by default
        $baseDocQuery = EmployeeDocument::whereHas('employee', function ($q) {
            $q->active();
        });

        $data['totalDocuments']    = (clone $baseDocQuery)->count();
        $data['approvedDocuments'] = (clone $baseDocQuery)->where('verification_status', 'Approved')->count();
        $data['pendingDocuments']  = (clone $baseDocQuery)->where('verification_status', 'Pending')->count();
        $data['rejectedDocuments'] = (clone $baseDocQuery)->where('verification_status', 'Rejected')->count();

        // Dropdown data: Active vs Inactive segmented
        $data['activeEmployees']   = Employee::active()->orderBy('employee_name')->get();
        $data['inactiveEmployees'] = Employee::where(function ($q) {
            $q->where('status', 'Inactive')
                ->orWhere('status', '0')
                ->orWhere('status', 0)
                ->orWhere('status', 'inactive')
                ->orWhereNotNull('deleted_at');
        })->orderBy('employee_name')->get();

        $data['employees']     = Employee::whereNull('deleted_at')->orderBy('employee_name')->get();
        $data['documentTypes'] = self::DOCUMENT_TYPES;

        return view('admin.documents.index', $data);
    }

    /**
     * Store a newly uploaded employee document.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id'         => 'required|exists:employees,employee_id',
            'document_type'       => 'required|string|max:100',
            'document_name'       => 'required|string|max:191',
            'document_number'     => 'nullable|string|max:100',
            'document_file'       => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:10240', // 10MB
            'issue_date'          => 'nullable|date',
            'expiry_date'         => 'nullable|date|after_or_equal:issue_date',
            'verification_status' => 'required|in:Pending,Approved,Rejected',
            'notes'               => 'nullable|string|max:1000',
        ]);

        $file = $request->file('document_file');
        $extension = strtolower($file->getClientOriginalExtension());
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();

        // Ensure target directory exists
        $uploadDir = public_path('uploads/documents');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }

        // Generate sanitized unique file name
        $sanitizedDocType = strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $validated['document_type']));
        $fileName = 'emp_' . $validated['employee_id'] . '_' . $sanitizedDocType . '_' . time() . '.' . $extension;
        $file->move($uploadDir, $fileName);
        $filePath = 'uploads/documents/' . $fileName;

        $doc = EmployeeDocument::create([
            'employee_id'         => $validated['employee_id'],
            'document_type'       => $validated['document_type'],
            'document_name'       => trim($validated['document_name']),
            'document_number'     => $validated['document_number'] ? trim($validated['document_number']) : null,
            'file_path'           => $filePath,
            'file_name'           => $originalName,
            'file_size'           => $fileSize,
            'file_extension'      => $extension,
            'issue_date'          => $validated['issue_date'] ?? null,
            'expiry_date'         => $validated['expiry_date'] ?? null,
            'verification_status' => $validated['verification_status'],
            'rejection_reason'    => null,
            'verified_by'         => ($validated['verification_status'] === 'Approved') ? (Auth::id() ?? 1) : null,
            'verified_at'         => ($validated['verification_status'] === 'Approved') ? now() : null,
            'notes'               => $validated['notes'] ?? null,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Document "' . $doc->document_name . '" uploaded to vault successfully!',
                'data'    => $doc,
            ]);
        }

        return redirect()->back()->with('success', 'Document uploaded successfully!');
    }

    /**
     * Display the specified document details for review drawer.
     */
    public function show(string $id)
    {
        $doc = EmployeeDocument::with(['employee.designation', 'verifier'])->findOrFail($id);
        $emp = $doc->employee;

        return response()->json([
            'success'  => true,
            'document' => [
                'id'                  => $doc->id,
                'employee_id'         => $doc->employee_id,
                'employee_name'       => $emp ? $emp->employee_name : 'Staff',
                'employee_code'       => '#EMP-' . $doc->employee_id,
                'designation'         => $emp && $emp->designation ? ($emp->designation->name ?? $emp->designation->designation_name) : 'Staff',
                'document_type'       => $doc->document_type,
                'document_name'       => $doc->document_name,
                'document_number'     => $doc->document_number ?: 'Not Provided',
                'file_name'           => $doc->file_name,
                'file_url'            => asset($doc->file_path),
                'file_extension'      => strtoupper($doc->file_extension),
                'file_size'           => $doc->formatted_file_size,
                'is_image'            => in_array(strtolower($doc->file_extension), ['jpg', 'jpeg', 'png', 'webp']),
                'issue_date'          => $doc->issue_date ? $doc->issue_date->format('d M, Y') : 'Not Specified',
                'expiry_date'         => $doc->expiry_date ? $doc->expiry_date->format('d M, Y') : 'No Expiry',
                'verification_status' => $doc->verification_status,
                'rejection_reason'    => $doc->rejection_reason,
                'verified_at'         => $doc->verified_at ? $doc->verified_at->format('d M, Y h:i A') : null,
                'verifier_name'       => $doc->verifier ? $doc->verifier->name : ($doc->verified_by ? 'HR Administrator' : null),
                'notes'               => $doc->notes ?: 'No additional notes recorded.',
                'uploaded_at'         => $doc->created_at ? $doc->created_at->format('d M, Y h:i A') : '-',
                'download_url'        => route('employees.documents.download', $doc->id),
            ]
        ]);
    }

    /**
     * Update verification status (Approve or Reject document).
     */
    public function verifyStatus(Request $request, string $id)
    {
        $doc = EmployeeDocument::findOrFail($id);

        $validated = $request->validate([
            'status'           => 'required|in:Approved,Rejected,Pending',
            'rejection_reason' => 'required_if:status,Rejected|nullable|string|max:1000',
        ]);

        $status = $validated['status'];
        $doc->verification_status = $status;

        if ($status === 'Approved') {
            $doc->rejection_reason = null;
            $doc->verified_by = Auth::id() ?? 1;
            $doc->verified_at = now();
            $msg = "Document '{$doc->document_name}' has been verified & approved successfully.";
        } elseif ($status === 'Rejected') {
            $doc->rejection_reason = $validated['rejection_reason'];
            $doc->verified_by = Auth::id() ?? 1;
            $doc->verified_at = now();
            $msg = "Document '{$doc->document_name}' has been marked as Rejected.";
        } else {
            $doc->rejection_reason = null;
            $doc->verified_by = null;
            $doc->verified_at = null;
            $msg = "Document '{$doc->document_name}' reset to Pending Verification.";
        }

        $doc->save();

        return response()->json([
            'success'   => true,
            'message'   => $msg,
            'newStatus' => $doc->verification_status,
        ]);
    }

    /**
     * Download the employee document.
     */
    public function download(string $id)
    {
        $doc = EmployeeDocument::findOrFail($id);
        $fullPath = public_path($doc->file_path);

        if (File::exists($fullPath)) {
            return response()->download($fullPath, $doc->file_name);
        }

        return redirect()->back()->with('error', 'Document file not found on server.');
    }

    /**
     * Remove the specified document from the vault.
     */
    public function destroy(Request $request, string $id)
    {
        $doc = EmployeeDocument::findOrFail($id);
        $docName = $doc->document_name;

        // Delete physical file if exists
        $fullPath = public_path($doc->file_path);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }

        $doc->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Document '{$docName}' removed from vault.",
            ]);
        }

        return redirect()->back()->with('success', "Document '{$docName}' deleted successfully.");
    }
}

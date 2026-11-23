@extends('layout.master')
@section('title', $title)
@section('main-section')
    <x-alert-modal :type="session('success') ? 'success' : (session('error') ? 'error' : '')" :message="session('success') ?? session('error')" />

    <style>
        .doc-preview-container {
            min-height: 180px;
            max-height: 280px;
            overflow: hidden;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .doc-preview-img {
            max-width: 100%;
            max-height: 260px;
            object-fit: contain;
        }
    </style>

    <div class="page-wrapper">
        <div class="content">
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">{{ $title }}</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item" aria-current="page">Employees / Document Vault</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="mb-2">
                        <button type="button" class="btn btn-primary d-flex align-items-center" id="btnOpenUploadModal">
                            <i class="ti ti-circle-plus me-2"></i>Upload Document
                        </button>
                    </div>
                </div>
            </div>

            <!-- KPI Summary Cards -->
            <div class="row">
                <div class="col-sm-6 col-xl-3 mb-3">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Total Documents</p>
                                <h4 class="mb-0" id="kpiTotalDocs">{{ $totalDocuments }}</h4>
                            </div>
                            <span class="avatar avatar-lg bg-primary-transparent"><i class="ti ti-files fs-20"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3 mb-3">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Verified / Approved</p>
                                <h4 class="mb-0" id="kpiApprovedDocs">{{ $approvedDocuments }}</h4>
                            </div>
                            <span class="avatar avatar-lg bg-success-transparent"><i class="ti ti-circle-check fs-20"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3 mb-3">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Pending Review</p>
                                <h4 class="mb-0" id="kpiPendingDocs">{{ $pendingDocuments }}</h4>
                            </div>
                            <span class="avatar avatar-lg bg-warning-transparent"><i class="ti ti-clock-hour-4 fs-20"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3 mb-3">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Rejected / Resubmit</p>
                                <h4 class="mb-0" id="kpiRejectedDocs">{{ $rejectedDocuments }}</h4>
                            </div>
                            <span class="avatar avatar-lg bg-danger-transparent"><i class="ti ti-alert-circle fs-20"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body p-0">
                            <div class="row g-2 align-items-end p-3">
                                <div class="col-md-2">
                                    <label class="form-label" for="filterEmployeeStatus">Staff</label>
                                    <select class="form-control" id="filterEmployeeStatus">
                                        <option value="active" selected>Active Staff</option>
                                        <option value="inactive">Inactive Staff</option>
                                        <option value="all">All Staff</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="filterEmployee">Employee</label>
                                    <select class="form-control" id="filterEmployee">
                                        <option value="all">All Employees</option>
                                        <optgroup label="Active Employees ({{ $activeEmployees->count() }})">
                                            @foreach ($activeEmployees as $emp)
                                                <option value="{{ $emp->employee_id }}">{{ $emp->employee_name }} (#EMP-{{ $emp->employee_id }})</option>
                                            @endforeach
                                        </optgroup>
                                        @if ($inactiveEmployees->count() > 0)
                                            <optgroup label="Inactive Staff ({{ $inactiveEmployees->count() }})">
                                                @foreach ($inactiveEmployees as $emp)
                                                    <option value="{{ $emp->employee_id }}">{{ $emp->employee_name }} (#EMP-{{ $emp->employee_id }}) [Inactive]</option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="filterDocType">Document Type</label>
                                    <select class="form-control" id="filterDocType">
                                        <option value="all">All Document Types</option>
                                        @foreach ($documentTypes as $type)
                                            <option value="{{ $type }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label" for="filterStatus">Status</label>
                                    <select class="form-control" id="filterStatus">
                                        <option value="all">All Status</option>
                                        <option value="Approved">Approved</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Rejected">Rejected</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-light d-flex align-items-center" id="btnResetFilters">
                                        <i class="ti ti-rotate-clockwise me-1"></i>Reset
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="documentsTable" class="table table-striped table-bordered nowrap">
                                    <thead>
                                        <tr>
                                            <th>Sn</th>
                                            <th>Employee</th>
                                            <th>Document</th>
                                            <th>File</th>
                                            <th>Validity</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-footer />
    </div>

    <!-- Upload Document Modal -->
    <div class="modal fade" id="upload_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Employee Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
        <form id="uploadDocForm" enctype="multipart/form-data" novalidate>
            @csrf

            <!-- Employee Selection -->
            <div class="mb-3">
                <label class="form-label fs-12 fw-semibold text-dark">Select Employee <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm rounded-2" id="inputEmployeeId" name="employee_id" required>
                    <option value="">-- Choose Employee --</option>
                    <optgroup label="Active Employees ({{ $activeEmployees->count() }})">
                        @foreach($activeEmployees as $emp)
                            <option value="{{ $emp->employee_id }}">{{ $emp->employee_name }} (#EMP-{{ $emp->employee_id }})</option>
                        @endforeach
                    </optgroup>
                    @if($inactiveEmployees->count() > 0)
                        <optgroup label="Inactive Staff ({{ $inactiveEmployees->count() }})">
                            @foreach($inactiveEmployees as $emp)
                                <option value="{{ $emp->employee_id }}">{{ $emp->employee_name }} (#EMP-{{ $emp->employee_id }}) [Inactive]</option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
                <div class="invalid-feedback fs-11">Please select an employee.</div>
            </div>

            <!-- Document Category / Type -->
            <div class="mb-3">
                <label class="form-label fs-12 fw-semibold text-dark">Document Type <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm rounded-2" id="inputDocType" name="document_type" required>
                    <option value="">-- Select Category --</option>
                    @foreach($documentTypes as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
                <div class="invalid-feedback fs-11">Please select a document category.</div>
            </div>

            <!-- Document Title / Name -->
            <div class="mb-3">
                <label class="form-label fs-12 fw-semibold text-dark">Document Title / Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm rounded-2" id="inputDocName" name="document_name"
                       placeholder="e.g. Government Identity - Aadhaar Card" required maxlength="191">
                <div class="invalid-feedback fs-11">Document title is required.</div>
            </div>

            <!-- Document Identifier / Number -->
            <div class="mb-3">
                <label class="form-label fs-12 fw-semibold text-dark">Document / ID Number (Optional)</label>
                <input type="text" class="form-control form-control-sm rounded-2 font-monospace" id="inputDocNumber" name="document_number"
                       placeholder="e.g. XXXX-XXXX-1234 or PAN ABCDE1234F" maxlength="100">
                <small class="text-muted fs-11">Unique identifier number printed on the credential.</small>
            </div>

            <!-- File Upload Input -->
            <div class="mb-3">
                <label class="form-label fs-12 fw-semibold text-dark">Attach Document File <span class="text-danger">*</span></label>
                <input type="file" class="form-control form-control-sm rounded-2" id="inputDocFile" name="document_file"
                       accept=".pdf,.jpg,.jpeg,.png,.webp" required>
                <small class="text-muted fs-11">Supported formats: PDF, JPG, PNG, WEBP (Max: 10MB)</small>
                <div class="invalid-feedback fs-11">Please select a file to upload.</div>
            </div>

            <!-- Issue & Expiry Dates -->
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label fs-12 fw-semibold text-dark">Issue Date</label>
                    <input type="date" class="form-control form-control-sm rounded-2" id="inputIssueDate" name="issue_date">
                </div>
                <div class="col-6">
                    <label class="form-label fs-12 fw-semibold text-dark">Expiry Date</label>
                    <input type="date" class="form-control form-control-sm rounded-2" id="inputExpiryDate" name="expiry_date">
                    <small class="text-muted fs-11">Required for Passport/Visa</small>
                </div>
            </div>

            <!-- Initial Verification Status -->
            <div class="mb-3">
                <label class="form-label fs-12 fw-semibold text-dark">Verification Status <span class="text-danger">*</span></label>
                <div class="d-flex align-items-center gap-3 mt-1">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="verification_status" id="uploadStatusApproved" value="Approved" checked>
                        <label class="form-check-label fs-13" for="uploadStatusApproved">
                            <span class="badge bg-success">Approved / Verified</span>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="verification_status" id="uploadStatusPending" value="Pending">
                        <label class="form-check-label fs-13" for="uploadStatusPending">
                            <span class="badge bg-warning text-dark">Pending Review</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="mb-3">
                <label class="form-label fs-12 fw-semibold text-dark">Verification Remarks &amp; Notes</label>
                <textarea class="form-control form-control-sm rounded-2" id="inputNotes" name="notes" rows="2"
                          placeholder="Add any verification observations, portal reference numbers, or compliance notes..." maxlength="1000"></textarea>
            </div>
        </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="btnSaveDocument">
                        <i class="ti ti-upload me-1"></i><span id="btnSaveText">Upload Document</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Document Modal -->
    <div class="modal fade" id="view_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Document Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="viewLoader" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                    <div id="viewContent" style="display:none;">
                        <div class="mb-4 pb-3 border-bottom">
                            <h4 class="mb-1" id="reviewDocName"></h4>
                            <div class="text-muted mb-1">Number: <span id="reviewDocNumber"></span></div>
                            <span id="reviewStatusBadge" class="badge"></span>
                        </div>

                        <h6 class="mb-3">Employee</h6>
                        <div class="row mb-3">
                            <div class="col-md-4 mb-3"><div class="text-muted small">Name</div><div class="fw-medium" id="reviewEmpName"></div></div>
                            <div class="col-md-4 mb-3"><div class="text-muted small">Employee Code</div><div class="fw-medium" id="reviewEmpCode"></div></div>
                            <div class="col-md-4 mb-3"><div class="text-muted small">Designation</div><div class="fw-medium" id="reviewEmpDesignation"></div></div>
                        </div>

                        <h6 class="mb-3">Attached File</h6>
                        <div class="row mb-3">
                            <div class="col-md-4 mb-3"><div class="text-muted small">File Name</div><div class="fw-medium text-break" id="reviewFileName"></div></div>
                            <div class="col-md-4 mb-3"><div class="text-muted small">Type</div><div class="fw-medium" id="reviewFileExt"></div></div>
                            <div class="col-md-4 mb-3"><div class="text-muted small">Size</div><div class="fw-medium" id="reviewFileSize"></div></div>
                            <div class="col-md-12">
                                <div class="doc-preview-container p-2" id="reviewPreviewContainer"></div>
                            </div>
                        </div>

                        <h6 class="mb-3">Validity &amp; Record</h6>
                        <div class="row mb-3">
                            <div class="col-md-3 col-6 mb-3"><div class="text-muted small">Issue Date</div><div class="fw-medium" id="reviewIssueDate"></div></div>
                            <div class="col-md-3 col-6 mb-3"><div class="text-muted small">Expiry Date</div><div class="fw-medium" id="reviewExpiryDate"></div></div>
                            <div class="col-md-3 col-6 mb-3"><div class="text-muted small">Uploaded On</div><div class="fw-medium" id="reviewUploadedDate"></div></div>
                            <div class="col-md-3 col-6 mb-3"><div class="text-muted small">Verified By</div><div class="fw-medium" id="reviewVerifiedBy"></div></div>
                        </div>

                        <h6 class="mb-2">Remarks / Notes</h6>
                        <p class="mb-3" id="reviewDocNotes"></p>

                        <div class="alert alert-danger d-none mb-3" id="reviewRejectionBox">
                            <div class="fw-semibold mb-1">Rejection Reason</div>
                            <div id="reviewRejectionReason">-</div>
                        </div>

                        <div class="mb-0 d-none" id="rejectionInputArea">
                            <label class="form-label" for="inputRejectionReason">Reason for rejection <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="inputRejectionReason" rows="2" maxlength="1000"
                                placeholder="Explain what is wrong so the employee can resubmit..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" id="hrDecisionCard">
                    <a href="#" id="reviewDownloadBtn" class="btn btn-outline-primary me-auto" target="_blank"><i class="ti ti-download me-1"></i>Download</a>
                    <button type="button" class="btn btn-outline-warning" id="btnResetToPending"><i class="ti ti-rotate-clockwise me-1"></i>Reset to Pending</button>
                    <button type="button" class="btn btn-outline-danger" id="btnRejectToggle"><i class="ti ti-x me-1"></i>Reject</button>
                    <button type="button" class="btn btn-success" id="btnApproveDoc"><i class="ti ti-check me-1"></i>Approve</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Full preview modal -->
    <div class="modal fade" id="documentPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0" id="previewModalTitle">Document Preview</h5>
                        <small class="text-muted" id="previewModalMetaInfo"></small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="previewModalContent"></div>
                </div>
                <div class="modal-footer">
                    <a href="#" id="previewModalDownloadBtn" class="btn btn-outline-primary" target="_blank"><i class="ti ti-download me-1"></i>Download</a>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="delete_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <span class="avatar avatar-xl bg-transparent-danger text-danger mb-3">
                        <i class="ti ti-trash-x fs-36"></i>
                    </span>
                    <h4 class="mb-1">Confirm Delete</h4>
                    <p class="mb-3">Are you sure you want to delete <strong id="deleteDocName"></strong>? The attached file will be permanently deleted.</p>
                    <input type="hidden" id="deleteDocId">
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Yes, Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after_scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            let currentReviewDocId = null;

            function showToast(message, type = 'success') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: type,
                        title: message,
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                } else {
                    alert(message);
                }
            }

            var table = $('#documentsTable').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                scrollX: true,
                ordering: false,
                ajax: {
                    url: "{{ route('employees.documents.index') }}",
                    data: function(d) {
                        d.employee_status = $('#filterEmployeeStatus').val() || 'active';
                        d.status = $('#filterStatus').val();
                        d.employee_id = $('#filterEmployee').val();
                        d.document_type = $('#filterDocType').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex' },
                    { data: 'employee' },
                    { data: 'document' },
                    { data: 'file' },
                    { data: 'validity' },
                    { data: 'status' },
                    { data: 'action', orderable: false, searchable: false }
                ]
            });

            function updateKpis() {
                $.get("{{ route('employees.documents.index') }}", {
                    kpi: 1,
                    employee_status: $('#filterEmployeeStatus').val() || 'active',
                    employee_id: $('#filterEmployee').val(),
                    document_type: $('#filterDocType').val()
                }, function(res) {
                    $('#kpiTotalDocs').text(res.total);
                    $('#kpiApprovedDocs').text(res.approved);
                    $('#kpiPendingDocs').text(res.pending);
                    $('#kpiRejectedDocs').text(res.rejected);
                }, 'json');
            }

            function reloadAll() {
                table.ajax.reload(null, false);
                updateKpis();
            }

            $('#filterEmployeeStatus, #filterEmployee, #filterDocType, #filterStatus').on('change', function() {
                table.ajax.reload();
                updateKpis();
            });

            $('#btnResetFilters').on('click', function() {
                $('#filterEmployeeStatus').val('active');
                $('#filterEmployee').val('all');
                $('#filterDocType').val('all');
                $('#filterStatus').val('all');
                table.search('').draw();
                updateKpis();
            });

            // ---------- Upload ----------
            $('#btnOpenUploadModal').on('click', function() {
                $('#uploadDocForm')[0].reset();
                $('#uploadDocForm').removeClass('was-validated');
                $('#uploadStatusApproved').prop('checked', true);
                $('#upload_modal').modal('show');
            });

            $('#btnSaveDocument').on('click', function() {
                const form = document.getElementById('uploadDocForm');
                if (!form.checkValidity()) {
                    form.classList.add('was-validated');
                    return;
                }

                const btn = $(this);
                const originalText = $('#btnSaveText').text();
                btn.prop('disabled', true);
                $('#btnSaveText').text('Uploading...');

                $.ajax({
                    url: "{{ route('employees.documents.store') }}",
                    type: 'POST',
                    data: new FormData(form),
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        btn.prop('disabled', false);
                        $('#btnSaveText').text(originalText);
                        if (response.success) {
                            showToast(response.message, 'success');
                            $('#upload_modal').modal('hide');
                            reloadAll();
                        } else {
                            showToast(response.message || 'Error uploading document', 'error');
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false);
                        $('#btnSaveText').text(originalText);
                        let msg = 'Error uploading document';
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.errors) {
                                msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                            } else if (xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                        }
                        showToast(msg, 'error');
                    }
                });
            });

            // ---------- Full preview ----------
            window.openLightboxModal = function(fileUrl, docName, fileName, fileSize, isImage) {
                $('#previewModalTitle').text(docName);
                $('#previewModalDownloadBtn').attr('href', fileUrl);
                $('#previewModalMetaInfo').text(fileName + ' • ' + fileSize);
                const $c = $('#previewModalContent').empty();
                if (isImage) {
                    $('<img>', { src: fileUrl, alt: docName, class: 'img-fluid rounded', css: { maxHeight: '72vh', objectFit: 'contain' } }).appendTo($c);
                } else {
                    $('<iframe>', { src: fileUrl, css: { width: '100%', height: '72vh', border: 'none' } }).appendTo($c);
                }
                $('#documentPreviewModal').modal('show');
            };

            // ---------- View ----------
            let currentDoc = null;

            window.viewDocumentDetails = function(id) {
                currentReviewDocId = id;
                $('#viewContent').hide();
                $('#viewLoader').show();
                $('#rejectionInputArea').addClass('d-none');
                $('#inputRejectionReason').val('');
                $('#btnRejectToggle').removeClass('btn-danger').addClass('btn-outline-danger').html('<i class="ti ti-x me-1"></i>Reject');
                $('#view_modal').modal('show');

                $.get("{{ url('employees/documents') }}/" + id, function(res) {
                    if (!(res.success && res.document)) {
                        return;
                    }
                    const d = res.document;
                    currentDoc = d;

                    $('#reviewDocName').text(d.document_name);
                    $('#reviewDocNumber').text(d.document_number);

                    let statusClass = 'bg-warning text-dark';
                    if (d.verification_status === 'Approved') statusClass = 'bg-success';
                    if (d.verification_status === 'Rejected') statusClass = 'bg-danger';
                    $('#reviewStatusBadge').attr('class', 'badge ' + statusClass).text(d.verification_status);

                    $('#reviewEmpName').text(d.employee_name);
                    $('#reviewEmpCode').text(d.employee_code);
                    $('#reviewEmpDesignation').text(d.designation);
                    $('#reviewFileName').text(d.file_name);
                    $('#reviewFileExt').text(d.file_extension);
                    $('#reviewFileSize').text(d.file_size);
                    $('#reviewDownloadBtn').attr('href', d.download_url);
                    $('#reviewIssueDate').text(d.issue_date);
                    $('#reviewExpiryDate').text(d.expiry_date);
                    $('#reviewUploadedDate').text(d.uploaded_at);
                    $('#reviewVerifiedBy').text(d.verifier_name || 'Pending Review');
                    $('#reviewDocNotes').text(d.notes || 'No additional notes recorded.');

                    const $preview = $('#reviewPreviewContainer').empty();
                    if (d.is_image) {
                        $('<img>', { src: d.file_url, alt: d.document_name, class: 'doc-preview-img rounded', css: { cursor: 'zoom-in' }, title: 'Click to view full image' })
                            .on('click', function() { openLightboxModal(d.file_url, d.document_name, d.file_name, d.file_size, true); })
                            .appendTo($preview);
                    } else {
                        $('<button>', { type: 'button', class: 'btn btn-primary', html: '<i class="ti ti-eye me-1"></i>Preview PDF' })
                            .on('click', function() { openLightboxModal(d.file_url, d.document_name, d.file_name, d.file_size, false); })
                            .appendTo($preview);
                    }

                    if (d.verification_status === 'Rejected' && d.rejection_reason) {
                        $('#reviewRejectionReason').text(d.rejection_reason);
                        $('#reviewRejectionBox').removeClass('d-none');
                    } else {
                        $('#reviewRejectionBox').addClass('d-none');
                    }

                    $('#viewLoader').hide();
                    $('#viewContent').show();
                }).fail(function() {
                    $('#view_modal').modal('hide');
                    showToast('Failed to load document details', 'error');
                });
            };

            // ---------- Verify ----------
            function submitVerification(status, reason) {
                if (!currentReviewDocId) return;
                $.ajax({
                    url: "{{ url('employees/documents') }}/" + currentReviewDocId + "/verify",
                    type: 'POST',
                    data: { status: status, rejection_reason: reason },
                    success: function(response) {
                        if (response.success) {
                            showToast(response.message, 'success');
                            $('#view_modal').modal('hide');
                            reloadAll();
                        } else {
                            showToast(response.message || 'Error updating status', 'error');
                        }
                    },
                    error: function(xhr) {
                        showToast(xhr.responseJSON ? xhr.responseJSON.message : 'Error processing verification', 'error');
                    }
                });
            }

            $('#btnRejectToggle').on('click', function() {
                const area = $('#rejectionInputArea');
                if (area.hasClass('d-none')) {
                    area.removeClass('d-none');
                    $('#inputRejectionReason').focus();
                    $(this).removeClass('btn-outline-danger').addClass('btn-danger').html('<i class="ti ti-x me-1"></i>Confirm Rejection');
                } else {
                    const reason = $('#inputRejectionReason').val().trim();
                    if (!reason) {
                        showToast('Please provide a reason for rejecting this document', 'error');
                        return;
                    }
                    submitVerification('Rejected', reason);
                }
            });

            $('#btnApproveDoc').on('click', function() { submitVerification('Approved', null); });
            $('#btnResetToPending').on('click', function() { submitVerification('Pending', null); });

            // ---------- Delete ----------
            $(document).on('click', '.btn-delete-doc', function() {
                $('#deleteDocId').val($(this).data('id'));
                $('#deleteDocName').text($(this).data('name'));
                $('#delete_modal').modal('show');
            });

            $('#confirmDeleteBtn').on('click', function() {
                const id = $('#deleteDocId').val();
                if (!id) return;
                $.ajax({
                    url: "{{ url('employees/documents') }}/" + id,
                    type: 'DELETE',
                    success: function(response) {
                        $('#delete_modal').modal('hide');
                        showToast(response.message || 'Document deleted', response.success ? 'success' : 'error');
                        reloadAll();
                    },
                    error: function(xhr) {
                        $('#delete_modal').modal('hide');
                        showToast(xhr.responseJSON ? xhr.responseJSON.message : 'Error deleting document', 'error');
                    }
                });
            });
        });
    </script>
@endpush

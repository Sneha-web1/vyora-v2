@extends('layout.master')
@section('title', $title)

@section('main-section')
    <x-alert-modal :type="session('success') ? 'success' : (session('error') ? 'error' : '')" :message="session('success') ?? session('error')" />

    <div class="page-wrapper">
        <div class="content">

            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">{{ $title }}</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item" aria-current="page">
                                {{ $titleRoute }}
                            </li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="mb-2">
                        <a href="{{ route('masters.organisation.policy.create') }}"
                            class="btn btn-primary d-flex align-items-center">
                            <i class="ti ti-circle-plus me-2"></i>Add New Policy
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="policyList" class="table table-striped table-bordered nowrap">
                            <thead>
                                <tr>
                                    <th>Sn</th>
                                    <th>Policy Title</th>
                                    <th>Policy Code</th>
                                    <th>Organization</th>
                                    <th>Branch</th>
                                    <th>Department</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
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

        <x-footer />
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
                    <p class="mb-3">Are you sure you want to delete this policy? This action cannot be undone.</p>
                    <input type="hidden" id="deletePolicyId">
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Yes, Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="view_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Policy Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="viewLoader" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                    <div id="viewContent" style="display:none;">
                        <div class="mb-4 pb-3 border-bottom">
                            <h4 class="mb-1" id="v_title"></h4>
                            <div class="text-muted mb-1">Code: <span id="v_code"></span></div>
                            <span id="v_status" class="badge"></span>
                        </div>

                        <h6 class="mb-3">Policy Information</h6>
                        <div class="row mb-3">
                            <div class="col-md-4 mb-3"><div class="text-muted small">Organization</div><div class="fw-medium" id="v_company"></div></div>
                            <div class="col-md-4 mb-3"><div class="text-muted small">Branch</div><div class="fw-medium" id="v_branch"></div></div>
                            <div class="col-md-4 mb-3"><div class="text-muted small">Department</div><div class="fw-medium" id="v_department"></div></div>
                            <div class="col-md-4 mb-3"><div class="text-muted small">Start Date</div><div class="fw-medium" id="v_effective_from"></div></div>
                            <div class="col-md-4 mb-3"><div class="text-muted small">End Date</div><div class="fw-medium" id="v_expiry_date"></div></div>
                            <div class="col-md-12 mb-3"><div class="text-muted small">Description</div><div class="fw-medium" id="v_description" style="white-space:pre-line;"></div></div>
                            <div class="col-md-12 mb-3"><div class="text-muted small">Policy Document</div><div class="fw-medium"><a id="v_document" href="#" target="_blank" rel="noopener"></a></div></div>
                        </div>

                        <h6 class="mb-3">Record Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2"><div class="text-muted small">Created At</div><div class="fw-medium" id="v_created_at"></div></div>
                            <div class="col-md-6 mb-2"><div class="text-muted small">Last Updated</div><div class="fw-medium" id="v_updated_at"></div></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" id="v_edit_btn" class="btn btn-outline-primary"><i class="ti ti-edit me-1"></i>Edit</a>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after_scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#policyList').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                scrollX: true,
                ajax: "{{ route('masters.organisation.policy.list') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'company',
                        name: 'company'
                    },
                    {
                        data: 'branch',
                        name: 'branch'
                    },
                    {
                        data: 'department',
                        name: 'department'
                    },
                    {
                        data: 'effective_from',
                        name: 'effective_from'
                    },
                    {
                        data: 'expiry_date',
                        name: 'expiry_date'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'id',
                        render: function(data, type, row) {
                            return `
                        <div class="action-icon d-inline-flex">
                            <a href="javascript:void(0);" onclick="viewPolicy(` + data + `)" class="me-2" title="View">
                                <i class="ti ti-eye"></i>
                            </a>
                            <a href="{{ url('master/policy/edit') }}/` + data + `" class="me-2" title="Edit">
                                <i class="ti ti-edit"></i>
                            </a>
                            <a href="javascript:void(0);" onclick="deletePolicy(` + data + `)" title="Delete">
                                <i class="ti ti-trash"></i>
                            </a>
                        </div>`;
                        },
                        orderable: false,
                        searchable: false
                    },
                ],
                order: [
                    [0, 'asc']
                ],
            });
        });

        function viewPolicy(id) {
            $('#viewContent').hide();
            $('#viewLoader').show();
            $('#view_modal').modal('show');

            $.get("{{ url('master/policy/view') }}/" + id, function(p) {
                var dash = function(x) { return (x === null || x === undefined || x === '') ? '--' : x; };
                ['title', 'code', 'company', 'branch', 'department', 'effective_from', 'expiry_date', 'description', 'created_at', 'updated_at']
                .forEach(function(f) { $('#v_' + f).text(dash(p[f])); });

                $('#v_status').text(p.status === 'Active' ? 'Active' : 'Inactive')
                    .removeClass('bg-success bg-danger')
                    .addClass(p.status === 'Active' ? 'bg-success' : 'bg-danger');

                if (p.document_url) {
                    $('#v_document').text('View / Download PDF').attr('href', p.document_url);
                } else {
                    $('#v_document').text('--').attr('href', '#');
                }
                $('#v_edit_btn').attr('href', p.edit_url);

                $('#viewLoader').hide();
                $('#viewContent').show();
            }).fail(function() {
                $('#view_modal').modal('hide');
                alert('Policy details load nahi ho paaye!');
            });
        }

        function deletePolicy(id) {
            $('#deletePolicyId').val(id);
            $('#delete_modal').modal('show');
        }

        $('#confirmDeleteBtn').click(function() {
            var id = $('#deletePolicyId').val();
            $.ajax({
                url: "{{ url('master/policy/delete') }}/" + id,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    $('#delete_modal').modal('hide');
                    $('#policyList').DataTable().ajax.reload();
                },
                error: function(err) {
                    alert(err.responseJSON?.message || 'Something went wrong!');
                }
            });
        });
    </script>
@endpush
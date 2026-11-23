@extends('layout.master')
@section('title', $title)
@section('main-section')
    <x-alert-modal />

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
                        <a href="{{ route('masters.organisation.company.create') }}"
                            class="btn btn-primary d-flex align-items-center">
                            <i class="ti ti-circle-plus me-2"></i> Add Company
                        </a>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive mt-3">
                                    <table id="comnayList" class="display table table-striped table-bordered nowrap">
                                        <thead>
                                            <tr>
                                                <th>Sn</th>
                                                <th>Company Logo</th>
                                                <th>Company Name</th>
                                                <th>Company Code</th>
                                                <th>Contact No</th>
                                                <th>Email</th>
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

        <div class="modal fade" id="delete_modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center">
                        <span class="avatar avatar-xl bg-transparent-danger text-danger mb-3">
                            <i class="ti ti-trash-x fs-36"></i>
                        </span>
                        <h4 class="mb-1">Confirm Delete</h4>
                        <p class="mb-3">Are you sure you want to delete this company? This action cannot be undone.</p>
                        <input type="hidden" id="deleteCompanyId">
                        <div class="d-flex justify-content-center">
                            <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Yes, Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="view_modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Company Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="viewLoader" class="text-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>
                        <div id="viewContent" style="display:none;">
                            <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                                <img id="v_logo" src="" alt="Logo" class="rounded border p-1 me-3"
                                    style="width:80px; height:80px; object-fit:contain;">
                                <div>
                                    <h4 class="mb-1" id="v_company_name"></h4>
                                    <div class="text-muted mb-1">Code: <span id="v_company_code"></span></div>
                                    <span id="v_status" class="badge"></span>
                                </div>
                            </div>

                            <h6 class="mb-3">Basic &amp; Contact Information</h6>
                            <div class="row mb-3">
                                <div class="col-md-4 mb-3"><div class="text-muted small">Contact No</div><div class="fw-medium" id="v_contact_no"></div></div>
                                <div class="col-md-4 mb-3"><div class="text-muted small">Landline No</div><div class="fw-medium" id="v_landline_no"></div></div>
                                <div class="col-md-4 mb-3"><div class="text-muted small">Email</div><div class="fw-medium"><a id="v_email" href="#"></a></div></div>
                                <div class="col-md-12 mb-3"><div class="text-muted small">Website</div><div class="fw-medium"><a id="v_website" href="#" target="_blank" rel="noopener"></a></div></div>
                            </div>

                            <h6 class="mb-3">Legal &amp; Tax Details</h6>
                            <div class="row mb-3">
                                <div class="col-md-3 col-6 mb-3"><div class="text-muted small">GST No</div><div class="fw-medium" id="v_gst_no"></div></div>
                                <div class="col-md-3 col-6 mb-3"><div class="text-muted small">PAN No</div><div class="fw-medium" id="v_pan_no"></div></div>
                                <div class="col-md-3 col-6 mb-3"><div class="text-muted small">CIN No</div><div class="fw-medium" id="v_cin_no"></div></div>
                                <div class="col-md-3 col-6 mb-3"><div class="text-muted small">IEC</div><div class="fw-medium" id="v_iec"></div></div>
                            </div>

                            <h6 class="mb-3">Address</h6>
                            <div class="row mb-3">
                                <div class="col-md-12 mb-3"><div class="text-muted small">Address</div><div class="fw-medium" id="v_address"></div></div>
                                <div class="col-md-3 col-6 mb-3"><div class="text-muted small">Country</div><div class="fw-medium" id="v_country"></div></div>
                                <div class="col-md-3 col-6 mb-3"><div class="text-muted small">State</div><div class="fw-medium" id="v_state"></div></div>
                                <div class="col-md-3 col-6 mb-3"><div class="text-muted small">City</div><div class="fw-medium" id="v_city"></div></div>
                                <div class="col-md-3 col-6 mb-3"><div class="text-muted small">Pincode</div><div class="fw-medium" id="v_pincode"></div></div>
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
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script>
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $(document).ready(function() {
                var table = $('#comnayList').DataTable({
                    responsive: true,
                    processing: true,
                    serverSide: true,
                    scrollX: true,
                    ajax: "{{ route('masters.organisation.company.list') }}",
                    columns: [{
                            data: 'DT_RowIndex'
                        },
                        {
                            data: 'logo'
                        },
                        {
                            data: 'company_name'
                        },
                        {
                            data: 'company_code'
                        },
                        {
                            data: 'contact_no'
                        },
                        {
                            data: 'email'
                        },
                        {
                            data: 'status'
                        },
                        {
                            data: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    dom: "<'row mb-2'<'col-md-6'l><'col-md-6 text-end'f>>" +
                        "<'row'<'col-md-12'tr>>" +
                        "<'row mt-2'<'col-md-5'i><'col-md-7'p>>",
                });

                window.viewCompany = function(id) {
                    $('#viewContent').hide();
                    $('#viewLoader').show();
                    $('#view_modal').modal('show');

                    $.get("{{ url('master/company/view') }}/" + id, function(c) {
                        var dash = function(v) { return (v === null || v === undefined || v === '') ? '--' : v; };
                        var fields = ['company_name', 'company_code', 'contact_no', 'landline_no', 'gst_no', 'pan_no',
                            'cin_no', 'iec', 'address', 'country', 'state', 'city', 'pincode', 'created_at', 'updated_at'
                        ];
                        fields.forEach(function(f) { $('#v_' + f).text(dash(c[f])); });

                        $('#v_logo').attr('src', c.logo);
                        $('#v_status').text(c.status === 'Active' ? 'Active' : 'Inactive')
                            .removeClass('bg-success bg-danger')
                            .addClass(c.status === 'Active' ? 'bg-success' : 'bg-danger');

                        $('#v_email').text(dash(c.email)).attr('href', c.email ? 'mailto:' + c.email : '#');
                        var safeSite = c.website && /^https?:\/\//i.test(c.website);
                        $('#v_website').text(dash(c.website)).attr('href', safeSite ? c.website : '#');
                        $('#v_edit_btn').attr('href', c.edit_url);

                        $('#viewLoader').hide();
                        $('#viewContent').show();
                    }).fail(function() {
                        $('#view_modal').modal('hide');
                        alert('Company details load nahi ho paaye!');
                    });
                }

                window.deleteCompany = function(id) {
                    $('#deleteCompanyId').val(id);
                    $('#delete_modal').modal('show');
                }

                $('#confirmDeleteBtn').click(function() {
                    var id = $('#deleteCompanyId').val();
                    $.ajax({
                        url: "{{ url('master/company/delete') }}/" + id,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            $('#delete_modal').modal('hide');
                            table.ajax.reload();
                        },
                        error: function(err) {
                            alert(err.responseJSON?.message || 'Something went wrong!');
                        }
                    });
                });
            });
        </script>
    @endpush

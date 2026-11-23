@extends('layout.master')
@section('title', $title ?? 'Agency Master')

@section('main-section')
    <x-alert-modal :type="session('success') ? 'success' : (session('error') ? 'error' : '')" :message="session('success') ?? session('error')" />

    <div class="page-wrapper">
        <div class="content">

            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">{{ $title }}</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item" aria-current="page">{{ $titleRoute }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                    <div class="mb-2">
                        <a href="{{ route('masters.organisation.agency.mapping') }}" class="btn btn-outline-secondary d-flex align-items-center">
                            <i class="ti ti-users me-2"></i>Employee Mapping
                        </a>
                    </div>
                    <div class="mb-2">
                        <a href="{{ route('masters.organisation.agency.create') }}" class="btn btn-primary d-flex align-items-center">
                            <i class="ti ti-circle-plus me-2"></i>Add Agency
                        </a>
                    </div>
                </div>
            </div>

            <!-- KPI Summary Cards -->
            <div class="row">
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Total Agencies</p>
                                <h4 class="mb-0">{{ number_format($totalAgencies ?? 0) }}</h4>
                            </div>
                            <span class="avatar avatar-lg bg-primary-transparent"><i class="ti ti-building fs-20"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Active Agencies</p>
                                <h4 class="mb-0">{{ number_format($activeAgencies ?? 0) }}</h4>
                            </div>
                            <span class="avatar avatar-lg bg-success-transparent"><i class="ti ti-circle-check fs-20"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Inactive Agencies</p>
                                <h4 class="mb-0">{{ number_format($inactiveAgencies ?? 0) }}</h4>
                            </div>
                            <span class="avatar avatar-lg bg-danger-transparent"><i class="ti ti-circle-x fs-20"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Mapped Employees</p>
                                <h4 class="mb-0">{{ number_format($mappedEmployees ?? 0) }}</h4>
                            </div>
                            <span class="avatar avatar-lg bg-info-transparent"><i class="ti ti-users fs-20"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="btn-group mb-2" role="group" id="statusGroup">
                        <button type="button" class="btn btn-primary" data-status="">All ({{ $totalAgencies }})</button>
                        <button type="button" class="btn btn-outline-primary" data-status="Active">Active ({{ $activeAgencies }})</button>
                        <button type="button" class="btn btn-outline-primary" data-status="Inactive">Inactive ({{ $inactiveAgencies }})</button>
                    </div>
                    <input type="hidden" id="statusFilter" value="">

                    <div class="table-responsive mt-2">
                        <table id="agencyList" class="display table table-striped table-bordered nowrap">
                            <thead>
                                <tr>
                                    <th>Sn</th>
                                    <th>Agency Name</th>
                                    <th>Agency Code</th>
                                    <th>Contact Person</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Location</th>
                                    <th>Employees</th>
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

    <!-- VIEW MODAL -->
    <div class="modal fade" id="view_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Agency Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="viewLoader" class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>
                    <div id="viewContent" style="display:none;">
                        <div class="mb-4 pb-3 border-bottom">
                            <h4 class="mb-1" id="v_agency_name"></h4>
                            <div class="text-muted mb-1">Code: <span id="v_agency_code"></span></div>
                            <span id="v_status" class="badge"></span>
                        </div>

                        <h6 class="mb-3">Contact Information</h6>
                        <div class="row mb-3">
                            <div class="col-md-4 mb-3"><div class="text-muted small">Contact Person</div><div class="fw-medium" id="v_contact_person"></div></div>
                            <div class="col-md-4 mb-3"><div class="text-muted small">Phone</div><div class="fw-medium" id="v_phone"></div></div>
                            <div class="col-md-4 mb-3"><div class="text-muted small">Email</div><div class="fw-medium" id="v_email"></div></div>
                        </div>

                        <h6 class="mb-3">Address</h6>
                        <div class="row mb-3">
                            <div class="col-md-12 mb-3"><div class="text-muted small">Address</div><div class="fw-medium" id="v_address"></div></div>
                            <div class="col-md-4 mb-3"><div class="text-muted small">Country</div><div class="fw-medium" id="v_country"></div></div>
                            <div class="col-md-4 mb-3"><div class="text-muted small">State</div><div class="fw-medium" id="v_state"></div></div>
                            <div class="col-md-4 mb-3"><div class="text-muted small">City</div><div class="fw-medium" id="v_city"></div></div>
                        </div>

                        <h6 class="mb-3">Other Information</h6>
                        <div class="row mb-3">
                            <div class="col-md-4 mb-3"><div class="text-muted small">Mapped Employees</div><div class="fw-medium" id="v_employees_count"></div></div>
                            <div class="col-md-8 mb-3"><div class="text-muted small">Notes / Terms</div><div class="fw-medium" id="v_notes"></div></div>
                        </div>

                        <h6 class="mb-3">Record Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3"><div class="text-muted small">Created At</div><div class="fw-medium" id="v_created_at"></div></div>
                            <div class="col-md-6 mb-3"><div class="text-muted small">Last Updated</div><div class="fw-medium" id="v_updated_at"></div></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary" id="v_edit_btn"><i class="ti ti-edit me-1"></i>Edit</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- STATUS MODAL -->
    <div class="modal fade" id="status_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <span class="avatar avatar-xl bg-transparent-warning text-warning mb-3"><i class="ti ti-power fs-36"></i></span>
                    <h4 class="mb-1" id="statusModalTitle">Change Status</h4>
                    <p class="mb-3" id="statusModalText"></p>
                    <input type="hidden" id="statusAgencyId">
                    <input type="hidden" id="statusNext">
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="confirmStatusBtn" class="btn btn-primary">Yes, Continue</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DELETE MODAL -->
    <div class="modal fade" id="delete_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <span class="avatar avatar-xl bg-transparent-danger text-danger mb-3"><i class="ti ti-trash-x fs-36"></i></span>
                    <h4 class="mb-1">Confirm Delete</h4>
                    <p class="mb-1">Are you sure you want to delete this agency? Employees mapped to it will be unmapped.</p>
                    <p class="mb-3 text-muted fs-12">This action cannot be undone.</p>
                    <div id="deleteError" class="alert alert-danger py-2 fs-12 d-none"></div>
                    <input type="hidden" id="deleteAgencyId">
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Yes, Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TOAST -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index:2000;">
        <div id="agencyToast" class="toast align-items-center border-0 text-white" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="agencyToastMsg"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
@endsection

@push('after_scripts')
    <style>
        #agencyList_wrapper .dt-paging,
        #agencyList_wrapper .dataTables_paginate { margin-left: auto; text-align: right; }
        #agencyList_wrapper .dt-paging nav { justify-content: flex-end; }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        const BASE = "{{ url('master/agency') }}";
        let table;

        function showToast(msg, ok = true) {
            const $t = $('#agencyToast');
            $t.removeClass('bg-success bg-danger').addClass(ok ? 'bg-success' : 'bg-danger');
            $('#agencyToastMsg').text(msg);
            bootstrap.Toast.getOrCreateInstance($t[0], { delay: 3000 }).show();
        }

        function dash(v) { return (v === null || v === undefined || v === '') ? '--' : v; }

        $(document).ready(function() {
            table = $('#agencyList').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                scrollX: true,
                ajax: {
                    url: "{{ route('masters.organisation.agency.list') }}",
                    data: function(d) { d.status = $('#statusFilter').val(); }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'agency_name', orderable: false },
                    { data: 'agency_code', orderable: false },
                    { data: 'contact_person', orderable: false },
                    { data: 'email', orderable: false },
                    { data: 'phone', orderable: false },
                    { data: 'location', orderable: false },
                    { data: 'employees', orderable: false, searchable: false },
                    { data: 'status', orderable: false, searchable: false },
                    { data: 'action', orderable: false, searchable: false }
                ],
                order: [],
                dom: "<'row mb-2'<'col-md-6'l><'col-md-6 text-end'f>>" +
                    "<'row'<'col-md-12'tr>>" +
                    "<'row mt-2'<'col-md-5'i><'col-md-7 d-flex justify-content-md-end'p>>",
            });

            $('#statusGroup button').on('click', function() {
                $('#statusFilter').val($(this).data('status'));
                $('#statusGroup button').removeClass('btn-primary').addClass('btn-outline-primary');
                $(this).removeClass('btn-outline-primary').addClass('btn-primary');
                table.ajax.reload();
            });
        });

        // ---------- View ----------
        function viewAgency(id) {
            $('#viewContent').hide();
            $('#viewLoader').show();
            $('#view_modal').modal('show');

            $.get(BASE + '/view/' + id, function(res) {
                const a = res.agency;
                ['agency_name', 'agency_code', 'contact_person', 'phone', 'email', 'address', 'country', 'city', 'state', 'notes', 'employees_count']
                    .forEach(function(f) { $('#v_' + f).text(dash(a[f])); });
                $('#v_created_at').text(res.meta.created_at);
                $('#v_updated_at').text(res.meta.updated_at);
                $('#v_status').text(a.status)
                    .removeClass('bg-success bg-danger')
                    .addClass(a.status === 'Active' ? 'bg-success' : 'bg-danger');
                $('#v_edit_btn').off('click').on('click', function() { window.location.href = BASE + '/edit/' + a.id; });

                $('#viewLoader').hide();
                $('#viewContent').show();
            }).fail(function() {
                $('#view_modal').modal('hide');
                showToast('Agency details load nahi ho paaye!', false);
            });
        }

        // ---------- Status ----------
        function openStatusModal(id, current) {
            const next = current === 'Active' ? 'Inactive' : 'Active';
            $('#statusAgencyId').val(id);
            $('#statusNext').val(next);
            $('#statusModalTitle').text('Make ' + next + '?');
            $('#statusModalText').html('This agency will be marked <strong>' + next + '</strong>.');
            $('#status_modal').modal('show');
        }

        $('#confirmStatusBtn').on('click', function() {
            const id = $('#statusAgencyId').val();
            const $btn = $(this).prop('disabled', true);
            $.ajax({
                url: BASE + '/status/' + id,
                type: 'POST',
                data: { status: $('#statusNext').val() },
                success: function(res) {
                    $('#status_modal').modal('hide');
                    showToast(res.message || 'Status updated!');
                    setTimeout(function() { window.location.reload(); }, 900);
                },
                error: function(xhr) {
                    $('#status_modal').modal('hide');
                    showToast(xhr.responseJSON?.message || 'Something went wrong!', false);
                },
                complete: function() { $btn.prop('disabled', false); }
            });
        });

        // ---------- Delete ----------
        function deleteAgency(id) {
            $('#deleteAgencyId').val(id);
            $('#deleteError').addClass('d-none').text('');
            $('#delete_modal').modal('show');
        }

        $('#confirmDeleteBtn').on('click', function() {
            const id = $('#deleteAgencyId').val();
            const $btn = $(this).prop('disabled', true);
            $.ajax({
                url: BASE + '/delete/' + id,
                type: 'POST',
                data: { _method: 'DELETE' },
                success: function(res) {
                    $('#delete_modal').modal('hide');
                    showToast(res.message || 'Agency deleted successfully!');
                    setTimeout(function() { window.location.reload(); }, 900);
                },
                error: function(xhr) {
                    $('#deleteError').removeClass('d-none').text(xhr.responseJSON?.message || 'Something went wrong!');
                },
                complete: function() { $btn.prop('disabled', false); }
            });
        });
    </script>
@endpush
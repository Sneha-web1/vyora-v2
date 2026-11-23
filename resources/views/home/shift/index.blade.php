@extends('layout.master')
@section('title', $title ?? 'Shift Master')

@section('main-section')
    <x-alert-modal :type="session('success') ? 'success' : (session('error') ? 'error' : '')" :message="session('success') ?? session('error')" />

    <div class="page-wrapper">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Shift Master</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item" aria-current="page">Master / Organisation / Shift</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                    <div class="mb-2">
                        <a href="{{ route('employee') }}" class="btn btn-outline-secondary d-flex align-items-center">
                            <i class="ti ti-users me-2"></i>Employees Roster
                        </a>
                    </div>
                    <div class="mb-2">
                        <a href="{{ route('masters.organisation.shift.create') }}" class="btn btn-primary d-flex align-items-center">
                            <i class="ti ti-circle-plus me-2"></i>Add Shift / Working Hours
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <!-- KPI Summary Cards -->
            <div class="row">
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Total Shifts</p>
                                <h4 class="mb-0">{{ number_format($totalShifts ?? 0) }}</h4>
                            </div>
                            <span class="avatar avatar-lg bg-primary-transparent"><i class="ti ti-clock fs-20"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Active Shifts</p>
                                <h4 class="mb-0">{{ number_format($activeShifts ?? 0) }}</h4>
                            </div>
                            <span class="avatar avatar-lg bg-success-transparent"><i class="ti ti-circle-check fs-20"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Inactive Shifts</p>
                                <h4 class="mb-0">{{ number_format($inactiveShifts ?? 0) }}</h4>
                            </div>
                            <span class="avatar avatar-lg bg-danger-transparent"><i class="ti ti-circle-x fs-20"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Assigned Staff</p>
                                <h4 class="mb-0">{{ number_format($assignedStaff ?? 0) }}</h4>
                            </div>
                            <span class="avatar avatar-lg bg-info-transparent"><i class="ti ti-users fs-20"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive mt-3">
                        <table id="shiftList" class="display table table-striped table-bordered nowrap">
                            <thead>
                                <tr>
                                    <th>Sn</th>
                                    <th>Shift Name</th>
                                    <th>Shift Code</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Grace Period</th>
                                    <th>Break Duration</th>
                                    <th>Total Working Hours</th>
                                    <th>Week Offs</th>
                                    <th>Staff Count</th>
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
                    <p class="mb-1">Are you sure you want to delete <strong id="deleteShiftName">this shift</strong>?</p>
                    <p class="mb-3 text-muted fs-12">This action cannot be undone.</p>
                    <div id="deleteShiftError" class="alert alert-danger py-2 fs-12 d-none"></div>
                    <input type="hidden" id="deleteShiftId">
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="confirmDeleteBtn" class="btn btn-danger">Yes, Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Toggle Modal -->
    <div class="modal fade" id="status_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <span class="avatar avatar-xl bg-transparent-warning text-warning mb-3">
                        <i class="ti ti-power fs-36"></i>
                    </span>
                    <h4 class="mb-1" id="statusModalTitle">Change Status</h4>
                    <p class="mb-1" id="statusModalText"></p>
                    <p class="mb-3 text-warning fs-12 d-none" id="statusModalWarn"></p>
                    <input type="hidden" id="statusShiftId">
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="confirmStatusBtn" class="btn btn-primary">Yes, Continue</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index:2000;">
        <div id="shiftToast" class="toast align-items-center border-0 text-white" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="shiftToastMsg"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
@endsection

@push('after_scripts')
    <style>
        #shiftList_wrapper .dt-paging,
        #shiftList_wrapper .dataTables_paginate { margin-left: auto; text-align: right; }
        #shiftList_wrapper .dt-paging nav { justify-content: flex-end; }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script>
        const CSRF = "{{ csrf_token() }}";
        const EDIT_URL = "{{ url('master/shift/edit') }}";
        const DELETE_URL = "{{ url('master/shift/delete') }}";
        const TOGGLE_URL = "{{ url('master/shift/toggle-status') }}";

        function esc(str) {
            return $('<div>').text(str ?? '').html();
        }

        function showToast(msg, ok = true) {
            const $t = $('#shiftToast');
            $t.removeClass('bg-success bg-danger').addClass(ok ? 'bg-success' : 'bg-danger');
            $('#shiftToastMsg').text(msg);
            bootstrap.Toast.getOrCreateInstance($t[0], {
                delay: 3000
            }).show();
        }

        $(document).ready(function() {
            $('#shiftList').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                scrollX: true,
                ajax: {
                    url: "{{ route('masters.organisation.shift.list') }}",
                    dataSrc: function(json) {
                        return json.data;
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'shift_name'
                    },
                    {
                        data: 'shift_code'
                    },
                    {
                        data: 'start_time'
                    },
                    {
                        data: 'end_time'
                    },
                    {
                        data: 'grace_period'
                    },
                    {
                        data: 'break_duration'
                    },
                    {
                        data: 'total_working_hours'
                    },
                    {
                        data: 'week_off_days',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'staff_count',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            if (type !== 'display') return data;
                            return `<a href="javascript:void(0);" class="text-decoration-none"
                                        onclick="openStatusModal(${row.id}, '${esc(row.shift_name).replace(/'/g, "\\'")}', ${row.is_active ? 'true' : 'false'}, ${row.staff_raw})">${data}</a>`;
                        }
                    },
                    {
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return `
                                <div class="action-icon d-inline-flex">
                                    <a href="${EDIT_URL}/${data}" class="me-2" title="Edit"><i class="ti ti-edit"></i></a>
                                    <a href="javascript:void(0);" title="Delete"
                                        onclick="deleteShift(${data}, '${esc(row.shift_name).replace(/'/g, "\\'")}')"><i class="ti ti-trash"></i></a>
                                </div>`;
                        }
                    },
                ],
                order: [],
                dom: "<'row mb-2'<'col-md-6'l><'col-md-6 text-end'f>>" +
                    "<'row'<'col-md-12'tr>>" +
                    "<'row mt-2'<'col-md-5'i><'col-md-7 d-flex justify-content-md-end'p>>",
            });
        });

        // ---------- Delete ----------
        function deleteShift(id, name) {
            $('#deleteShiftId').val(id);
            $('#deleteShiftName').text(name || 'this shift');
            $('#deleteShiftError').addClass('d-none').text('');
            $('#delete_modal').modal('show');
        }

        $('#confirmDeleteBtn').on('click', function() {
            const id = $('#deleteShiftId').val();
            const $btn = $(this).prop('disabled', true);
            $.ajax({
                url: DELETE_URL + '/' + id,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: CSRF
                },
                success: function(res) {
                    $('#delete_modal').modal('hide');
                    showToast(res.message || 'Shift deleted successfully!');
                    setTimeout(function() { window.location.reload(); }, 900);
                },
                error: function(xhr) {
                    $('#deleteShiftError').removeClass('d-none')
                        .text(xhr.responseJSON?.message || 'Something went wrong!');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });

        // ---------- Active / Inactive ----------
        function openStatusModal(id, name, isActive, staff) {
            $('#statusShiftId').val(id);
            const next = isActive ? 'Inactive' : 'Active';
            $('#statusModalTitle').text('Make ' + next + '?');
            $('#statusModalText').html('Shift <strong>' + name + '</strong> will be marked <strong>' + next + '</strong>.');
            if (isActive && staff > 0) {
                $('#statusModalWarn').removeClass('d-none')
                    .text(staff + ' employee(s) are currently assigned to this shift.');
            } else {
                $('#statusModalWarn').addClass('d-none').text('');
            }
            $('#confirmStatusBtn').toggleClass('btn-warning', isActive).toggleClass('btn-success', !isActive)
                .removeClass('btn-primary');
            $('#status_modal').modal('show');
        }

        $('#confirmStatusBtn').on('click', function() {
            const id = $('#statusShiftId').val();
            const $btn = $(this).prop('disabled', true);
            $.ajax({
                url: TOGGLE_URL + '/' + id,
                type: 'POST',
                data: {
                    _token: CSRF
                },
                success: function(res) {
                    $('#status_modal').modal('hide');
                    showToast(res.message || 'Status updated!');
                    $('#shiftList').DataTable().ajax.reload(null, false);
                    setTimeout(function() { window.location.reload(); }, 900);
                },
                error: function(xhr) {
                    $('#status_modal').modal('hide');
                    showToast(xhr.responseJSON?.message || 'Something went wrong!', false);
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });
    </script>
@endpush

@extends('layout.master')
@section('title', $title ?? 'Employee Mapping with Agency')

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
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="mb-2">
                        <a href="{{ route('masters.organisation.agency') }}" class="btn btn-primary d-flex align-items-center">
                            <i class="ti ti-arrow-left me-2"></i>Back to Agencies
                        </a>
                    </div>
                </div>
            </div>

            <!-- KPI Summary Cards -->
            <div class="row">
                <div class="col-xl-4 col-sm-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Total Employees</p>
                                <h4 class="mb-0">{{ number_format($totalCount) }}</h4>
                            </div>
                            <span class="avatar avatar-lg bg-primary-transparent"><i class="ti ti-users fs-20"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-sm-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Mapped to Agencies</p>
                                <h4 class="mb-0">{{ number_format($mappedCount) }}</h4>
                            </div>
                            <span class="avatar avatar-lg bg-success-transparent"><i class="ti ti-building fs-20"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-sm-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Direct / Unassigned</p>
                                <h4 class="mb-0">{{ number_format($unmappedCount) }}</h4>
                            </div>
                            <span class="avatar avatar-lg bg-warning-transparent"><i class="ti ti-user-check fs-20"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div>
                        <!-- Filter -->
                        <div class="row g-2 align-items-center mb-2">
                            <div class="col-md-4 col-sm-6">
                                <select id="agencyFilterSelect" class="form-select" onchange="window.location.href = this.value">
                                    <option value="{{ route('masters.organisation.agency.mapping') }}">All Employees</option>
                                    <option value="{{ route('masters.organisation.agency.mapping', ['filter_agency' => 'unassigned']) }}" {{ request('filter_agency') === 'unassigned' ? 'selected' : '' }}>
                                        Direct / Unassigned Only
                                    </option>
                                    @foreach ($agencies as $agency)
                                        <option value="{{ route('masters.organisation.agency.mapping', ['filter_agency' => $agency->id]) }}" {{ request('filter_agency') == $agency->id ? 'selected' : '' }}>
                                            {{ $agency->agency_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="table-responsive mt-2">
                            <table id="mappingTable" class="display table table-striped table-bordered nowrap">
                                <thead>
                                    <tr>
                                        <th>Sn</th>
                                        <th>Employee Name</th>
                                        <th>Email</th>
                                        <th>Assigned Agency</th>
                                        <th>Designation</th>
                                        <th>Type</th>
                                        <th>Contact</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($employees as $employee)
                                        @php $isActive = ($employee->status === 'Active'); @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $employee->employee_name }}</td>
                                            <td>{{ $employee->employee_email ?: '--' }}</td>
                                            <td>{{ $employee->agency->agency_name ?? 'Direct / In-House' }}</td>
                                            <td>{{ $designationNames[$employee->designation_id] ?? '--' }}</td>
                                            <td>{{ $employee->employment_type ?: '--' }}</td>
                                            <td>{{ $employee->employee_mobile ?: '--' }}</td>
                                            <td><span class="badge {{ $isActive ? 'bg-success' : 'bg-danger' }}">{{ $isActive ? 'Active' : 'Inactive' }}</span></td>
                                            <td>
                                                <button type="button" class="btn" title="Change Agency"
                                                    onclick="openAssignModal({{ $employee->employee_id }}, @js($employee->employee_name), '{{ $employee->agency_id ?? 'unassign' }}')">
                                                    <i class="ti ti-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <x-footer />
    </div>

    <!-- Assign Modal -->
    <div class="modal fade" id="assign_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{ route('masters.organisation.agency.mapping.save') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Change Agency</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Employee: <strong id="assignEmpName"></strong></p>
                        <input type="hidden" name="employee_ids[]" id="assignEmpId">
                        <label class="form-label">Agency</label>
                        <select name="target_agency_id" id="assignAgencySelect" class="form-select" required>
                            <option value="unassign">Direct / In-House (Unassign)</option>
                            @foreach ($agencies as $agency)
                                <option value="{{ $agency->id }}">{{ $agency->agency_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('after_scripts')
    <style>
        #mappingTable_wrapper .dt-paging,
        #mappingTable_wrapper .dataTables_paginate { margin-left: auto; text-align: right; }
        #mappingTable_wrapper .dt-paging nav { justify-content: flex-end; }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#mappingTable').DataTable({
                responsive: true,
                scrollX: true,
                order: [],
                columnDefs: [{ targets: -1, orderable: false, searchable: false }],
                dom: "<'row mb-2'<'col-md-6'l><'col-md-6 text-end'f>>" +
                    "<'row'<'col-md-12'tr>>" +
                    "<'row mt-2'<'col-md-5'i><'col-md-7 d-flex justify-content-md-end'p>>",
            });

        });

        function openAssignModal(id, name, agencyId) {
            document.getElementById('assignEmpId').value = id;
            document.getElementById('assignEmpName').textContent = name;
            document.getElementById('assignAgencySelect').value = agencyId;
            $('#assign_modal').modal('show');
        }
    </script>
@endpush
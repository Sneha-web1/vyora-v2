@extends('layout.master')
@section('title', $title ?? 'Employee Directory')

@section('main-section')
    <x-alert-modal :type="session('success') ? 'success' : (session('error') ? 'error' : '')" :message="session('success') ?? session('error')" />

    <style>
        /* Employee details modal */
        #employeeSidebar .modal-body {
            padding: 0 !important;
            background: #f8f9fa;
        }

        .emp-slider-hero {
            background: linear-gradient(145deg, #332c6d 0%, #241f4b 100%);
            position: relative;
            padding: 26px 24px 22px;
            color: #ffffff;
            overflow: hidden;
        }

        .emp-slider-hero::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.10) 0%, rgba(255, 255, 255, 0) 70%);
            pointer-events: none;
        }

        .emp-slider-hero::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: -30px;
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.10) 0%, rgba(255, 255, 255, 0) 70%);
            pointer-events: none;
        }

        .emp-avatar-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 8px;
        }

        .emp-avatar-img {
            width: 84px;
            height: 84px;
            border-radius: 8px;
            border: 3px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
            object-fit: cover;
            background: #ffffff;
        }

        .emp-status-floating-dot {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid #ffffff;
            background-color: #03c95a;
            box-shadow: 0 0 8px rgba(3, 201, 90, 0.6);
        }

        /* Quick Action Bar */
        .emp-quick-action-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            padding: 14px 20px;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
        }

        .emp-quick-action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px 6px;
            border-radius: 8px;
            background: #f8f9fa;
            border: 1px solid #e5e7eb;
            color: #374151;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            font-size: 11px;
            font-weight: 600;
        }

        .emp-quick-action-btn i {
            font-size: 18px;
            margin-bottom: 4px;
            transition: transform 0.2s ease;
        }

        .emp-quick-action-btn:hover {
            
            background: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            color: #332c6d;
            border-color: #d1d5db;
        }

        .emp-quick-action-btn:hover i {
            transform: scale(1.15);
        }

        /* Detail Cards in Slider */
        .emp-slider-section {
            padding: 20px;
        }

        .emp-slider-card {
            background: #ffffff;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            padding: 16px;
            margin-bottom: 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            transition: all 0.2s ease;
        }

        .emp-slider-card:hover {
            border-color: #d1d5db;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.04);
        }

        .emp-card-title {
            font-size: 11px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .emp-data-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #f3f4f6;
        }

        .emp-data-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .emp-data-row:first-child {
            padding-top: 0;
        }

        .emp-data-label {
            font-size: 12px;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
        }

        .emp-data-value {
            font-size: 13px;
            color: #111827;
            font-weight: 600;
            text-align: right;
        }

        /* Agency Highlight Card inside Slider */
        .agency-highlight-card {
            background: #f1f0f6;
            border: 1px solid #b9b5d6 !important;
            border-radius: 8px;
            padding: 16px;
            position: relative;
            overflow: hidden;
            margin-bottom: 14px;
        }

        /* Drawer Sticky Footer */
        .emp-slider-footer {
            background: #ffffff;
            border-top: 1px solid #e5e7eb;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Avatar / File Dropzone */
        .avatar-dropzone {
            border: 2px dashed #d1d5db;
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .avatar-dropzone:hover,
        .avatar-dropzone.dragover {
            background-color: #f3f4f6;
            border-color: #332c6d;
        }

        .avatar-dropzone .upload-icon {
            font-size: 2.2rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
            transition: transform 0.2s ease;
        }

        .avatar-dropzone:hover .upload-icon {
            
            color: #332c6d;
        }
    </style>

        <div class="page-wrapper">
        <div class="content">

            {{-- Header & Breadcrumbs (same pattern as Organization / Agency masters) --}}
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">{{ $title ?? 'Employee Directory' }}</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item" aria-current="page">Employees / Employee Directory</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                    <div class="mb-2">
                        <button type="button" class="btn btn-outline-secondary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#bulkImportModal">
                            <i class="ti ti-file-spreadsheet me-2"></i>Bulk Import
                        </button>
                    </div>
                    <div class="mb-2">
                        <a href="{{ route('employees.create') }}" class="btn btn-primary d-flex align-items-center">
                            <i class="ti ti-circle-plus me-2"></i>Add Employee
                        </a>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ti ti-check me-1"></i> {!! session('success') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="ti ti-alert-triangle me-1"></i> {!! session('warning') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ti ti-alert-circle me-1"></i> {!! session('error') !!}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- KPI Summary Cards -->
            <div class="row">
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card h-100" data-status="">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Total Employees</p>
                                <h4 class="mb-0">{{ number_format($totalEmployees ?? 0) }}</h4>
                            </div>
                            <span class="avatar avatar-lg bg-primary-transparent"><i class="ti ti-users fs-20"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card h-100" data-status="Active">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Active Employees</p>
                                <h4 class="mb-0">{{ number_format($activeEmployees ?? 0) }}</h4>
                            </div>
                            <span class="avatar avatar-lg bg-success-transparent"><i class="ti ti-circle-check fs-20"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card h-100" data-status="Inactive">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Inactive Employees</p>
                                <h4 class="mb-0">{{ number_format($inactiveEmployees ?? 0) }}</h4>
                            </div>
                            <span class="avatar avatar-lg bg-danger-transparent"><i class="ti ti-circle-x fs-20"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Full-Time Employees</p>
                                <h4 class="mb-0">{{ number_format($fulltimeEmployees ?? 0) }}</h4>
                            </div>
                            <span class="avatar avatar-lg bg-info-transparent"><i class="ti ti-id-badge fs-20"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body p-0">
                            <div class="row g-2 align-items-end p-3">
                                <div class="col-md-3">
                                    <label class="form-label" for="statusFilterSelect">Status</label>
                                    <select class="form-control" id="statusFilterSelect">
                                        <option value="">All</option>
                                        <option value="Active" {{ ($selectedStatus ?? 'Active') === 'Active' ? 'selected' : '' }}>Active</option>
                                        <option value="Inactive" {{ ($selectedStatus ?? '') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="agencyFilterSelect">Agency</label>
                                    <select class="form-control" id="agencyFilterSelect">
                                        <option value="">All Agencies</option>
                                        <option value="direct">Direct / In-House</option>
                                        @foreach ($agencies ?? [] as $agency)
                                            <option value="{{ $agency->id }}">{{ $agency->agency_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="designationFilterSelect">Designation</label>
                                    <select class="form-control" id="designationFilterSelect">
                                        <option value="">All Designations</option>
                                        @foreach ($designations ?? [] as $desig)
                                            <option value="{{ $desig->id }}">{{ $desig->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex gap-2">
                                    <button type="button" id="refreshTableBtn" class="btn btn-light d-flex align-items-center">
                                        <i class="ti ti-rotate-clockwise me-1"></i>Reset
                                    </button>
                                    <a href="{{ route('masters.organisation.agency.mapping') }}" class="btn btn-outline-secondary d-flex align-items-center">
                                        <i class="ti ti-users me-1"></i>Agency Mapping
                                    </a>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="employeeList" class="table table-striped table-bordered nowrap">
                                    <thead>
                                        <tr>
                                            <th>Sn</th>
                                            <th>Employee</th>
                                            <th>Assigned Agency</th>
                                            <th>Designation</th>
                                            <th>Joining Date</th>
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

    <!-- Modern Employee Profile Slider Drawer -->
    <div class="modal fade" id="employeeSidebar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0">Employee Details</h5>
                        <small class="text-muted" id="empCodeSubText">ID: #EMP---</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" id="employeeDetailsBody">
            <!-- Hero Gradient Card -->
            <div class="emp-slider-hero">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div class="emp-avatar-wrapper">
                        <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png" alt="Profile" 
                             class="emp-avatar-img" id="empProfile">
                        <span class="emp-status-floating-dot" id="empStatusDot"></span>
                    </div>
                    <div class="d-flex flex-column align-items-end gap-1.5">
                        <span class="badge bg-opacity-15 text-white border border-white border-opacity-20 font-monospace fs-11" id="empIdBadge">
                            #EMP---
                        </span>
                        <span class="badge bg-success-subtle text-success fs-11 px-2.5 py-1 rounded-pill fw-bold" id="empStatus">
                            Active
                        </span>
                    </div>
                </div>

                <h4 class="fw-bold text-white mb-1" id="empName">Employee Name</h4>
                <div class="text-white-50 fs-13 mb-3 d-flex align-items-center gap-1.5 flex-wrap">
                    <span id="empEmail"><i class="ti ti-mail me-1"></i>email@example.com</span>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge  bg-opacity-20 text-white border border-white border-opacity-25 fs-11 px-2.5 py-1 rounded-pill" id="empDesignation">
                        <i class="ti ti-briefcase me-1"></i>Designation
                    </span>
                    <span class="badge fs-11 text-white border border-white border-opacity-25 px-2.5 py-1 rounded-pill" id="empAgencyHeaderBadge" style="background-color: rgba(51, 44, 109, 0.45);">
                        <i class="ti ti-building me-1"></i>Agency
                    </span>
                    <span class="badge bg-white bg-opacity-10 text-white-50 border border-white border-opacity-15 fs-11 px-2 py-1 rounded-pill" id="empTypeBadge">
                        Full-time
                    </span>
                    <span class="badge  bg-opacity-15 text-white border border-white border-opacity-25 fs-11 px-2.5 py-1 rounded-pill" id="empLocationHeaderBadge" style="display: none;">
                        <i class="ti ti-map-pin me-1 text-warning"></i><span id="empLocationHeaderVal">-</span>
                    </span>
                </div>
            </div>

            <!-- Quick Action Hub -->
            <div class="emp-quick-action-bar">
                <a href="javascript:void(0);" id="quickCallBtn" class="emp-quick-action-btn" title="Call Mobile">
                    <i class="ti ti-phone text-success"></i>
                    <span>Call</span>
                </a>
                <a href="javascript:void(0);" id="quickEmailBtn" class="emp-quick-action-btn" title="Send Email">
                    <i class="ti ti-mail text-primary"></i>
                    <span>Email</span>
                </a>
                <a href="javascript:void(0);" id="quickReportBtn" class="emp-quick-action-btn" title="Month Attendance Sheet">
                    <i class="ti ti-calendar-stats text-warning"></i>
                    <span>Sheet</span>
                </a>
                <a href="javascript:void(0);" id="quickEditBtn" class="emp-quick-action-btn" title="Edit Profile">
                    <i class="ti ti-edit text-info"></i>
                    <span>Edit</span>
                </a>
            </div>

            <!-- Slider Content Sections -->
            <div class="emp-slider-section">

                <!-- Agency Highlight Card -->
                <div class="agency-highlight-card" id="agencyCardSection">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="fs-11 fw-bold text-uppercase tracking-wider" style="color: #332c6d;">
                            <i class="ti ti-building-skyscraper me-1"></i>Staff Agency Assignment
                        </span>
                        <a href="{{ route('masters.organisation.agency.mapping') }}" class="badge bg-white text-dark border text-decoration-none shadow-xs" title="Change Agency Mapping">
                            <i class="ti ti-arrows-exchange me-0.5"></i>Change
                        </a>
                    </div>
                    <h6 class="fw-bold text-dark mb-0.5" id="empAgencyTitle">Direct / In-House</h6>
                    <p class="text-muted fs-12 mb-0" id="empAgencySubtitle">Organization Staff Member</p>
                </div>

                <!-- Employment & Organization Details Card -->
                <div class="emp-slider-card">
                    <div class="emp-card-title">
                        <i class="ti ti-briefcase text-primary fs-15"></i>Employment Details
                    </div>

                    <div class="emp-data-row">
                        <span class="emp-data-label"><i class="ti ti-id-badge text-muted"></i>Designation</span>
                        <span class="emp-data-value" id="empDesignationVal">-</span>
                    </div>
                    <div class="emp-data-row">
                        <span class="emp-data-label"><i class="ti ti-clock text-muted"></i>Employment Type</span>
                        <span class="emp-data-value" id="empTypeVal">-</span>
                    </div>
                    <div class="emp-data-row">
                        <span class="emp-data-label"><i class="ti ti-calendar text-muted"></i>Joining Date</span>
                        <span class="emp-data-value" id="empJoiningVal">-</span>
                    </div>
                    <div class="emp-data-row">
                        <span class="emp-data-label"><i class="ti ti-calendar-check text-muted"></i>Confirmation Date</span>
                        <span class="emp-data-value" id="empConfirmationVal">-</span>
                    </div>
                    <div class="emp-data-row">
                        <span class="emp-data-label"><i class="ti ti-hourglass-empty text-muted"></i>Service Tenure</span>
                        <span class="emp-data-value text-primary font-monospace" id="empTenureVal">-</span>
                    </div>
                    <div class="emp-data-row">
                        <span class="emp-data-label"><i class="ti ti-clock-hour-4 text-info"></i>Work Shift</span>
                        <span class="emp-data-value text-info fw-semibold" id="empShiftVal">-</span>
                    </div>
                </div>

                <!-- Supervisor & Hierarchy Card -->
                <div class="emp-slider-card" id="supervisorCardSection">
                    <div class="emp-card-title d-flex align-items-center justify-content-between">
                        <span><i class="ti ti-crown text-warning fs-15 me-1"></i>Hierarchy & Supervision</span>
                        <span class="badge border py-0.5 px-2 fs-10" id="empSupervisorRoleBadge">Standard Employee</span>
                    </div>

                    <div class="emp-data-row" id="empReportingToRow">
                        <span class="emp-data-label"><i class="ti ti-user-check text-muted"></i>Reports To</span>
                        <span class="emp-data-value fw-semibold text-dark" id="empReportingSupervisorVal">-</span>
                    </div>

                    <div id="empSubordinatesSection" style="display: none;">
                        <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                            <span class="fs-12 fw-semibold text-dark"><i class="ti ti-users me-1 text-primary"></i>Supervised Team Members</span>
                            <span class="badge bg-primary-subtle text-primary" id="empSubordinatesCountBadge">0 Members</span>
                        </div>
                        <div id="empSubordinatesList" class="mt-2 d-flex flex-column gap-1.5" style="max-height: 200px; overflow-y: auto;">
                            <!-- Dynamically loaded list of subordinates -->
                        </div>
                    </div>
                </div>

                <!-- Contact & Personal Details Card -->
                <div class="emp-slider-card">
                    <div class="emp-card-title">
                        <i class="ti ti-user text-success fs-15"></i>Contact & Personal Information
                    </div>

                    <div class="emp-data-row">
                        <span class="emp-data-label"><i class="ti ti-phone text-muted"></i>Mobile Number</span>
                        <span class="emp-data-value" id="empMobileVal">-</span>
                    </div>
                    <div class="emp-data-row">
                        <span class="emp-data-label"><i class="ti ti-mail text-muted"></i>Email Address</span>
                        <span class="emp-data-value text-break" id="empEmailVal">-</span>
                    </div>
                    <div class="emp-data-row">
                        <span class="emp-data-label"><i class="ti ti-gender-bigender text-muted"></i>Gender</span>
                        <span class="emp-data-value" id="empGenderVal">-</span>
                    </div>
                    <div class="emp-data-row">
                        <span class="emp-data-label"><i class="ti ti-droplet text-danger"></i>Blood Group</span>
                        <span class="emp-data-value text-danger fw-semibold" id="empBloodGroupVal">-</span>
                    </div>
                </div>

                <!-- Posting Location Card -->
                <div class="emp-slider-card">
                    <div class="emp-card-title d-flex align-items-center justify-content-between flex-wrap gap-1">
                        <div>
                            <i class="ti ti-map-pin text-danger fs-15 me-1"></i>Posting Location
                        </div>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-11 fw-semibold" id="empFullLocationBadge" style="display: none;">
                            <i class="ti ti-current-location me-1"></i><span id="empFullLocationText">-</span>
                        </span>
                    </div>

                    <div class="emp-data-row">
                        <span class="emp-data-label"><i class="ti ti-building text-muted"></i>City</span>
                        <span class="emp-data-value fw-semibold text-dark" id="empCityVal">-</span>
                    </div>
                    <div class="emp-data-row">
                        <span class="emp-data-label"><i class="ti ti-map text-muted"></i>State</span>
                        <span class="emp-data-value" id="empStateVal">-</span>
                    </div>
                    <div class="emp-data-row">
                        <span class="emp-data-label"><i class="ti ti-world text-muted"></i>Country</span>
                        <span class="emp-data-value" id="empCountryVal">-</span>
                    </div>
                </div>

            </div>
        </div>

                <div class="modal-footer">
                    <a href="javascript:void(0);" id="editEmpLink" class="btn btn-outline-primary"><i class="ti ti-edit me-1"></i>Edit</a>
                    <a href="javascript:void(0);" id="sheetEmpLink" class="btn btn-outline-secondary" title="View Full Month Attendance Sheet"><i class="ti ti-calendar-stats me-1"></i>Month Sheet</a>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="delete_modal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content rounded border-0 shadow-sm">
                <div class="modal-body text-center p-4">
                    <span class="avatar avatar-xl rounded-circle bg-danger-subtle text-danger mb-3 d-inline-flex align-items-center justify-content-center">
                        <i class="ti ti-trash-x fs-32"></i>
                    </span>
                    <h5 class=" mb-1 card-title">Delete Employee?</h5>
                    <p class="text-muted fs-13 mb-4">Are you sure you want to delete this employee record? This action cannot be undone.</p>
                    <input type="hidden" id="deleteEmployeeId">
                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-light px-3" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-danger px-3 fw-semibold" id="confirmDeleteBtn">Yes, Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Change Modal -->
    <div class="modal fade" id="status_modal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content rounded border-0 shadow-sm">
                <div class="modal-body text-center p-4">
                    <span class="avatar avatar-xl rounded-circle bg-warning-subtle text-warning mb-3 d-inline-flex align-items-center justify-content-center">
                        <i class="ti ti-user-exclamation fs-32"></i>
                    </span>
                    <h5 class=" mb-1 card-title">Update Status</h5>
                    <p class="text-muted fs-13 mb-4" id="statusConfirmText">Are you sure you want to update status?</p>
                    <input type="hidden" id="statusEmployeeId">
                    <input type="hidden" id="statusValue">
                    <div class="d-flex justify-content-center gap-2">
                        <button class="btn btn-light px-3" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary px-3 fw-semibold" id="confirmStatusBtn">Yes, Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Import Modal -->
    <div class="modal fade" id="bulkImportModal" tabindex="-1" aria-labelledby="bulkImportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded shadow-sm overflow-hidden">
                <div class="modal-header bg-light bg-opacity-75 py-3 px-4 border-bottom">
                    <div class="d-flex align-items-center gap-2.5">
                        <span class="avatar avatar-md rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center">
                            <i class="ti ti-file-spreadsheet fs-22"></i>
                        </span>
                        <div>
                            <h5 class="modal-title  mb-0 card-title" id="bulkImportModalLabel">Bulk Import Employees & Payroll Data</h5>
                            <p class="text-muted fs-12 mb-0">Upload Excel (.xlsx, .xls) or CSV (.csv) to register employees along with optional Salary CTC, PF, UAN, and Bank details.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ route('employees.import') }}" method="POST" enctype="multipart/form-data" id="bulkImportForm">
                    @csrf
                    <div class="modal-body p-4">

                        {{-- Step 1: Download Sample File --}}
                        <div class="p-3 mb-4 rounded border bg-light bg-opacity-50 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2.5">
                                <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 fs-11 fw-bold">Step 1</span>
                                <div>
                                    <div class="fw-semibold text-dark fs-13">Download Complete Template (.csv)</div>
                                    <div class="text-muted fs-12">Pre-formatted template with employee profile, designations, agencies, and complete salary / PF columns.</div>
                                </div>
                            </div>
                            <a href="{{ route('employees.import.sample') }}" class="btn btn-sm btn-outline-primary fw-semibold d-inline-flex align-items-center">
                                <i class="ti ti-download me-1.5"></i>Download Template (.csv)
                            </a>
                        </div>

                        {{-- Step 2: Upload File Area --}}
                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label fw-bold text-dark fs-13 mb-0 d-flex align-items-center gap-2">
                                    <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 fs-11 fw-bold">Step 2</span>
                                    Select or Drag & Drop File <span class="text-danger">*</span>
                                </label>
                                <span class="text-muted fs-11">Formats: .xlsx, .xls, .csv &bull; Max: 10MB</span>
                            </div>

                            <div class="avatar-dropzone p-4 rounded" id="importDropzone" onclick="document.getElementById('employeeImportFileInput').click();">
                                <div id="importDropzonePrompt">
                                    <i class="ti ti-cloud-upload upload-icon text-primary d-block mb-2" style="font-size: 2.5rem;"></i>
                                    <h6 class="fw-bold text-dark mb-1">Click to browse or drag file here</h6>
                                    <p class="text-muted fs-12 mb-0">Supported files: Microsoft Excel (.xlsx, .xls) and Comma-Separated Values (.csv)</p>
                                </div>
                                <div id="importFilePreview" class="d-none">
                                    <div class="d-flex align-items-center justify-content-center gap-3 py-2">
                                        <i class="ti ti-file-spreadsheet text-success fs-36"></i>
                                        <div class="text-start">
                                            <div class="fw-bold text-dark fs-14" id="importFileName">file.xlsx</div>
                                            <div class="text-muted fs-12" id="importFileSize">0 KB</div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-light text-danger border ms-2" onclick="removeImportFile(event)">
                                            <i class="ti ti-trash me-1"></i>Remove
                                        </button>
                                    </div>
                                </div>
                                <input type="file" name="employee_file" id="employeeImportFileInput" class="d-none" accept=".csv, .xlsx, .xls, text/csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" onchange="handleImportFileSelect(this)">
                            </div>
                        </div>

                        {{-- Instructions & Column Mapping Help --}}
                        <div class="card border rounded bg-light bg-opacity-25 mb-0">
                            <div class="card-header">
                                <span class="fs-12 fw-bold text-dark d-flex align-items-center gap-1.5">
                                    <i class="ti ti-info-circle text-info"></i> Column Mapping & Smart Upsert Guidelines
                                </span>
                                <span class="badge bg-success-subtle text-success fs-10">Smart Upsert & Salary Enabled</span>
                            </div>
                            <div class="card-body fs-12 text-muted">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <h6 class="fs-11 fw-bold text-uppercase text-dark mb-1"><i class="ti ti-user me-1 text-primary"></i>Profile Info</h6>
                                        <ul class="mb-0 ps-3">
                                            <li><strong>Employee Name <span class="text-danger">*</span></strong></li>
                                            <li><strong>Employee Email <span class="text-danger">*</span></strong></li>
                                            <li><strong>Mobile Number</strong></li>
                                            <li><strong>Password</strong> (default 123456)</li>
                                            <li><strong>Gender & Blood Group</strong></li>
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <h6 class="fs-11 fw-bold text-uppercase text-dark mb-1"><i class="ti ti-briefcase me-1 text-info"></i>Role & Org</h6>
                                        <ul class="mb-0 ps-3">
                                            <li><strong>Designation <span class="text-danger">*</span></strong></li>
                                            <li><strong>Assigned Agency</strong></li>
                                            <li><strong>Work Shift & Type</strong></li>
                                            <li><strong>Joining Date</strong></li>
                                            <li><strong>Reporting Supervisor</strong></li>
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <h6 class="fs-11 fw-bold text-uppercase text-dark mb-1"><i class="ti ti-coin me-1 text-success"></i>Salary CTC</h6>
                                        <ul class="mb-0 ps-3">
                                            <li><strong>Monthly Gross</strong> (Auto calculates Basic 50%, HRA 40%, etc.)</li>
                                            <li><strong>Basic Salary & HRA</strong></li>
                                            <li><strong>Conveyance / Medical / Special</strong></li>
                                            <li><strong>Annual CTC</strong></li>
                                        </ul>
                                    </div>
                                    <div class="col-md-3">
                                        <h6 class="fs-11 fw-bold text-uppercase text-dark mb-1"><i class="ti ti-shield-check me-1 text-warning"></i>PF, ESI & Bank</h6>
                                        <ul class="mb-0 ps-3">
                                            <li><strong>PF No & UAN Number</strong></li>
                                            <li><strong>ESI Number & PAN Number</strong></li>
                                            <li><strong>Employee / Employer PF (12%)</strong></li>
                                            <li><strong>Bank Name, Account & IFSC</strong></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer bg-light bg-opacity-50 py-3 px-4 border-top d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="submitImportBtn" class="btn btn-primary btn-sm px-4 fw-semibold d-inline-flex align-items-center" disabled>
                            <i class="ti ti-upload me-1.5"></i>Start Bulk Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('after_scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script>
        $(document).ready(function() {
            var table = $('#employeeList').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                scrollX: true,
                ordering: false,
                ajax: {
                    url: "{{ route('employees.index') }}",
                    data: function(d) {
                        d.status = $('#statusFilterSelect').val();
                        d.agency_id = $('#agencyFilterSelect').val();
                        d.designation_id = $('#designationFilterSelect').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex' },
                    { data: 'employee' },
                    { data: 'agency' },
                    { data: 'designation' },
                    { data: 'joining_date' },
                    { data: 'status' },
                    { data: 'action', orderable: false, searchable: false }
                ]
            });

            $('#statusFilterSelect, #agencyFilterSelect, #designationFilterSelect').on('change', function() {
                table.ajax.reload();
            });

            // KPI cards quick filter
            $('[data-status]').filter('.card').css('cursor', 'pointer').on('click', function() {
                $('#statusFilterSelect').val($(this).data('status')).trigger('change');
            });

            $('#refreshTableBtn').on('click', function() {
                $('#statusFilterSelect').val('Active');
                $('#agencyFilterSelect').val('');
                $('#designationFilterSelect').val('');
                table.search('').draw();
            });

            // Auto-open drawer if ?view=ID or ?employee_id=ID is in URL
            const urlParams = new URLSearchParams(window.location.search);
            const autoViewId = urlParams.get('view') || urlParams.get('employee_id');
            if (autoViewId) {
                setTimeout(function() {
                    viewEmployee(autoViewId);
                }, 300);
            }
        });

        // View Employee in Drawer
        function viewEmployee(id) {
            $.ajax({
                url: "{{ url('employees') }}/" + id,
                type: "GET",
                success: function(emp) {
                    let imgUrl = (emp.profile_image && emp.profile_image !== 'default.png')
                        ? `{{ asset('uploads/employees') }}/${emp.profile_image}`
                        : `https://ui-avatars.com/api/?name=${encodeURIComponent(emp.employee_name || 'Staff')}&background=332c6d&color=fff&size=128&bold=true&length=1`;

                    $('#empProfile').attr('src', imgUrl);
                    $('#empName').text(emp.employee_name || '-');
                    $('#empEmail').html(`<i class="ti ti-mail me-1"></i>${emp.employee_email || '-'}`);
                    $('#empEmailVal').text(emp.employee_email || '-');
                    $('#empMobileVal').text(emp.employee_mobile || '-');
                    $('#empCodeSubText').text(`ID: #EMP-${emp.employee_id}`);
                    $('#empIdBadge').text(`#EMP-${emp.employee_id}`);

                    // Gender & Blood Group
                    const genderMap = { 1: 'Male', 2: 'Female', 3: 'Other' };
                    $('#empGenderVal').text(genderMap[emp.employee_gender] || '-');
                    $('#empBloodGroupVal').text(emp.blood_group || '-');

                    // Location
                    $('#empCityVal').text(emp.city_name || '-');
                    $('#empStateVal').text(emp.state_name || '-');
                    $('#empCountryVal').text(emp.country_name || '-');

                    if (emp.full_location && emp.full_location !== '-') {
                        $('#empLocationHeaderVal').text(emp.full_location);
                        $('#empLocationHeaderBadge').show();
                        $('#empFullLocationText').text(emp.full_location);
                        $('#empFullLocationBadge').show();
                    } else {
                        $('#empLocationHeaderBadge').hide();
                        $('#empFullLocationBadge').hide();
                    }

                    // Dates & Tenure
                    const joining = emp.joining_date ? new Date(emp.joining_date) : null;
                    const joiningStr = joining && !isNaN(joining)
                        ? joining.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
                        : (emp.joining_date || '-');
                    $('#empJoiningVal').text(joiningStr);

                    const confirmDate = emp.date_of_confirmation ? new Date(emp.date_of_confirmation) : null;
                    const confirmStr = confirmDate && !isNaN(confirmDate)
                        ? confirmDate.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
                        : (emp.date_of_confirmation || '-');
                    $('#empConfirmationVal').text(confirmStr);

                    if (joining && !isNaN(joining)) {
                        const now = new Date();
                        let months = (now.getFullYear() - joining.getFullYear()) * 12 + (now.getMonth() - joining.getMonth());
                        if (months < 0) months = 0;
                        const yrs = Math.floor(months / 12);
                        const remMonths = months % 12;
                        let tenureText = '';
                        if (yrs > 0) tenureText += `${yrs} yr${yrs > 1 ? 's' : ''} `;
                        tenureText += `${remMonths} mo${remMonths > 1 ? 's' : ''}`;
                        $('#empTenureVal').text(tenureText.trim() || '< 1 month');
                    } else {
                        $('#empTenureVal').text('-');
                    }

                    // Type & Designation
                    const empType = emp.employment_type || 'Full-time';
                    const designation = emp.designation_name || 'Staff Member';
                    $('#empTypeBadge').text(empType);
                    $('#empTypeVal').text(empType);
                    $('#empDesignation').html(`<i class="ti ti-briefcase me-1"></i>${designation}`);
                    $('#empDesignationVal').text(designation);
                    $('#empShiftVal').text(emp.shift_display || (emp.shift_name ? `${emp.shift_name} (${emp.shift_timing})` : 'General Shift (09:30 AM - 06:30 PM)'));

                    // Agency
                    if (emp.has_agency && emp.agency_name && emp.agency_name !== 'Direct / In-House') {
                        const codeSuffix = emp.agency_code ? ` (${emp.agency_code})` : '';
                        $('#empAgencyHeaderBadge').html(`<i class="ti ti-building me-1"></i>${emp.agency_name}`).show();
                        $('#empAgencyTitle').text(emp.agency_name + codeSuffix);
                        $('#empAgencySubtitle').text('Assigned Third-Party / Vendor Agency');
                    } else {
                        $('#empAgencyHeaderBadge').html(`<i class="ti ti-building-community me-1"></i>Direct / In-House`).show();
                        $('#empAgencyTitle').text('Direct / In-House');
                        $('#empAgencySubtitle').text('Internal Organization Employees (No Agency)');
                    }

                    // Supervisor & Hierarchy
                    if (emp.is_supervisor) {
                        $('#empSupervisorRoleBadge').html('<i class="ti ti-crown me-1 text-warning"></i>Supervisor').css({
                            'background-color': '#fff6dc',
                            'color': '#a37400',
                            'border-color': '#ffe9a8'
                        });
                        if (emp.subordinates && emp.subordinates.length > 0) {
                            $('#empSubordinatesCountBadge').text(`${emp.subordinates.length} Members`);
                            let subListHtml = '';
                            emp.subordinates.forEach(sub => {
                                subListHtml += `
                                    <div class="d-flex align-items-center justify-content-between p-2 rounded-2 border bg-white">
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-light text-secondary border me-1.5 font-monospace fs-10">#${sub.employee_id}</span>
                                            <div>
                                                <div class="fw-semibold text-dark fs-12">${sub.employee_name}</div>
                                                <div class="text-muted fs-11">${sub.designation_name} &bull; ${sub.agency_name}</div>
                                            </div>
                                        </div>
                                        <span class="badge ${sub.status === 'Inactive' ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success'} fs-10">${sub.status}</span>
                                    </div>
                                `;
                            });
                            $('#empSubordinatesList').html(subListHtml);
                            $('#empSubordinatesSection').show();
                        } else {
                            $('#empSubordinatesCountBadge').text('0 Members');
                            $('#empSubordinatesList').html('<div class="text-muted fs-11 p-2 text-center bg-white rounded border">No team members are currently mapped to this supervisor.</div>');
                            $('#empSubordinatesSection').show();
                        }
                    } else {
                        $('#empSupervisorRoleBadge').text('Standard Employee').css({
                            'background-color': '#f3f4f6',
                            'color': '#4b5563',
                            'border-color': '#d1d5db'
                        });
                        $('#empSubordinatesSection').hide();
                    }

                    if (emp.supervisor_name && emp.supervisor_name !== '-') {
                        const desig = emp.supervisor_designation ? ` (${emp.supervisor_designation})` : '';
                        $('#empReportingSupervisorVal').html(`<span class="text-primary fw-semibold"><i class="ti ti-user-check me-1"></i>${emp.supervisor_name}${desig}</span>`);
                        $('#empReportingToRow').show();
                    } else {
                        $('#empReportingSupervisorVal').html('<span class="text-muted">Direct / Independent (No Supervisor)</span>');
                        $('#empReportingToRow').show();
                    }

                    // Status
                    const isActive = (emp.status === 'Active' || emp.status === '1' || emp.status === 1);
                    $('#empStatus').text(isActive ? 'Active' : 'Inactive')
                                   .removeClass('bg-success-subtle text-success bg-danger-subtle text-danger')
                                   .addClass(isActive ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger');
                    $('#empStatusDot').css('background-color', isActive ? '#03c95a' : '#e70d0d');

                    // Quick Actions
                    // DPDP: number is masked, so the quick-call / quick-mail shortcuts are disabled
                    $('#quickCallBtn').attr('href', 'javascript:void(0);').addClass('disabled opacity-50');

                    $('#quickEmailBtn').attr('href', 'javascript:void(0);').addClass('disabled opacity-50');

                    const monthReportUrl = `{{ route('attendance.register.index') }}?employee_id=${emp.employee_id}`;
                    $('#quickReportBtn').attr('href', monthReportUrl);
                    $('#sheetEmpLink').attr('href', monthReportUrl);
                    $('#quickEditBtn').attr('href', `{{ url('employees/edit') }}/${emp.employee_id}`);
                    $('#editEmpLink').attr('href', `{{ url('employees/edit') }}/${emp.employee_id}`);

                    $('#employeeSidebar').modal('show');
                }
            });
        }

        // Status Toggle
        function openStatusModal(id, status) {
            let actionText = (status === 'Active') ? 'deactivate' : 'activate';
            $('#statusEmployeeId').val(id);
            $('#statusValue').val(status === 'Active' ? 0 : 1);
            $('#statusConfirmText').text(`Are you sure you want to ${actionText} this employee account?`);
            $('#status_modal').modal('show');
        }

        $('#confirmStatusBtn').click(function() {
            let id = $('#statusEmployeeId').val();
            let status = $('#statusValue').val();
            $.ajax({
                url: "{{ url('employees/status') }}/" + id,
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    status: status
                },
                success: function() {
                    $('#status_modal').modal('hide');
                    $('#employeeList').DataTable().ajax.reload(null, false);
                }
            });
        });

        // Delete Employee
        function deleteEmployee(id) {
            $('#deleteEmployeeId').val(id);
            $('#delete_modal').modal('show');
        }

        $('#confirmDeleteBtn').click(function() {
            let id = $('#deleteEmployeeId').val();
            $.ajax({
                url: "{{ url('employees/delete') }}/" + id,
                type: "POST",
                data: {
                    _method: "DELETE",
                    _token: "{{ csrf_token() }}"
                },
                success: function() {
                    $('#delete_modal').modal('hide');
                    $('#employeeList').DataTable().ajax.reload(null, false);
                }
            });
        });

        // Bulk Import Dropzone Preview & Logic
        function handleImportFileSelect(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (file.size > 10 * 1024 * 1024) {
                    alert('File size exceeds 10 MB limit.');
                    input.value = '';
                    return;
                }

                $('#importFileName').text(file.name);
                $('#importFileSize').text((file.size / 1024).toFixed(1) + ' KB');
                $('#importDropzonePrompt').addClass('d-none');
                $('#importFilePreview').removeClass('d-none');
                $('#submitImportBtn').prop('disabled', false);
            }
        }

        function removeImportFile(e) {
            if (e) e.stopPropagation();
            $('#employeeImportFileInput').val('');
            $('#importFilePreview').addClass('d-none');
            $('#importDropzonePrompt').removeClass('d-none');
            $('#submitImportBtn').prop('disabled', true);
        }

        $('#bulkImportForm').on('submit', function() {
            const btn = $('#submitImportBtn');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1.5" role="status" aria-hidden="true"></span>Importing Employees...');
        });

        const importDropzone = document.getElementById('importDropzone');
        if (importDropzone) {
            ['dragenter', 'dragover'].forEach(eventName => {
                importDropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    importDropzone.classList.add('dragover');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                importDropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    importDropzone.classList.remove('dragover');
                }, false);
            });

            importDropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files && files.length) {
                    const input = document.getElementById('employeeImportFileInput');
                    input.files = files;
                    handleImportFileSelect(input);
                }
            }, false);
        }
    </script>
@endpush

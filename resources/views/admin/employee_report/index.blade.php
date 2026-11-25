@extends('layout.master')
@section('title', $title ?? 'Employees Daily Reports')

@section('main-section')
    <x-alert-modal :type="session('success') ? 'success' : (session('error') ? 'error' : '')" :message="session('success') ?? session('error')" />

    <style>

        .employee-report-page .work-text {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            white-space: normal;
            word-break: break-word;
            font-size: 13px;
            line-height: 1.45;
            color: #374151;
        }


        /* Day Group Headers */
        .employee-report-page #dailyReportList tbody tr.day-group-header td {
            padding: 10px 16px !important;
            font-size: 13px !important;
            border-top: 1.5px solid #e5e7eb !important;
            border-bottom: 1.5px solid #e5e7eb !important;
        }


        .employee-report-page tr.day-group-normal td {
            background: #f8f9fa !important;
            color: #111827 !important;
        }


        .employee-report-page tr.day-group-saturday td {
            background: #f0fcf5 !important;
            color: #028a3f !important;
        }


        .employee-report-page tr.day-group-sunday td {
            background: #fdeeee !important;
            color: #8f0808 !important;
        }


        .employee-report-page .table-hover tbody tr:hover:not(.day-group-header) {
            background-color: rgba(51, 44, 109, 0.02) !important;
        }</style>

    <div class="page-wrapper employee-report-page">
        <div class="content">

            {{-- Page Header --}}
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">{{ $title ?? 'Daily Work Reports' }}</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item" aria-current="page">Employees / Daily Reports</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                </div>
            </div>

            {{-- Search & Filter Toolbar --}}
            <div class="card filter-card mb-4">
                <div class="card-body">
                    <form id="reportFilterForm" class="row g-3 align-items-end">
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label fs-13 fw-semibold text-dark mb-1">
                                <i class="ti ti-user me-1 text-primary"></i>Select Employee <span class="text-danger">*</span>
                            </label>
                            <select class="form-select select2" id="employeeFilter" required>
                                <option value="">-- Choose Employee --</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->employee_id }}">
                                        {{ $employee->employee_name }} ({{ $employee->masked_email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-6">
                            <label class="form-label fs-13 fw-semibold text-dark mb-1">
                                <i class="ti ti-calendar me-1 text-info"></i>From Date
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="ti ti-calendar-event"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" id="startDate"
                                    value="{{ \Carbon\Carbon::now()->startOfMonth()->format('d-m-Y') }}">
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-3 col-sm-6">
                            <label class="form-label fs-13 fw-semibold text-dark mb-1">
                                <i class="ti ti-calendar-due me-1 text-info"></i>To Date
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="ti ti-calendar-event"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" id="endDate"
                                    value="{{ \Carbon\Carbon::now()->endOfMonth()->format('d-m-Y') }}">
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-12 d-flex gap-2">
                            <button type="button" class="btn btn-primary w-100 fw-semibold d-inline-flex align-items-center justify-content-center" id="searchBtn">
                                <i class="ti ti-search me-1.5"></i>Generate
                            </button>
                            <button type="button" class="btn btn-success fw-semibold d-inline-flex align-items-center justify-content-center" id="exportBtn" title="Export">
                                <i class="ti ti-file-export"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Summary KPI Boxes (Shown dynamically after filter) --}}
            <div class="row d-none" id="summaryKpiRow">
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Total Logged Hours</p>
                                <h4 class="mb-0" id="kpiTotalHours">00:00</h4>
                            </div>
                            <span class="avatar avatar-lg bg-primary-transparent"><i class="ti ti-clock-check fs-20 fs-20"></i></span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Working Days Logged</p>
                                <h4 class="mb-0" id="kpiDaysLogged">0</h4>
                            </div>
                            <span class="avatar avatar-lg bg-success-transparent"><i class="ti ti-calendar-check fs-20 fs-20"></i></span>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Days Not Submitted</p>
                                <h4 class="mb-0" id="kpiDaysMissing">0</h4>
                            </div>
                            <span class="avatar avatar-lg bg-danger-transparent"><i class="ti ti-calendar-x fs-20 fs-20"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Report Table Card (same layout as Employee Directory) --}}
            <div class="card report-table-card">
                <div class="card-body p-0">
                    <h5 class="p-3 mb-0 border-bottom" id="tableHeaderTitle">Daily Work Log Sheets</h5>

                    <div class="table-responsive">
                        <table id="dailyReportList" class="table table-striped table-bordered nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Project Name</th>
                                    <th>Work Description</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th class="text-end">Duration</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-end fw-bold fs-14">Month Total Hours:</th>
                                    <th id="totalHours" class="fw-bold text-primary fs-15">00:00</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        </div>
        <x-footer />
    </div>

    <!-- Daily Report Details Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Daily Report Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <div class="p-3 bg-light rounded border">
                                <div class="text-muted fs-11 text-uppercase fw-semibold mb-1"><i class="ti ti-calendar me-1 text-primary"></i>Report Date</div>
                                <div class="fw-bold text-dark fs-14" id="modalReportDate">-</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 bg-light rounded border">
                                <div class="text-muted fs-11 text-uppercase fw-semibold mb-1"><i class="ti ti-clock-edit me-1 text-info"></i>Submission Date</div>
                                <div class="fw-bold text-dark fs-14" id="modalFilledDate">-</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="p-3 bg-light rounded border">
                                <div class="text-muted fs-11 text-uppercase fw-semibold mb-1"><i class="ti ti-folder me-1 text-warning"></i>Project</div>
                                <div class="fw-bold text-dark fs-14" id="modalProject">-</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-2.5 bg-light rounded border text-center">
                                <div class="text-muted fs-11 text-uppercase fw-semibold mb-1">Start Time</div>
                                <div class="fw-bold text-dark fs-13" id="modalStartTime">-</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-2.5 bg-light rounded border text-center">
                                <div class="text-muted fs-11 text-uppercase fw-semibold mb-1">End Time</div>
                                <div class="fw-bold text-dark fs-13" id="modalEndTime">-</div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="p-2.5 bg-primary-subtle text-primary rounded border border-primary-subtle text-center">
                                <div class="fs-11 text-uppercase fw-semibold mb-1">Total Duration</div>
                                <div class="fw-bold fs-14" id="modalHours">-</div>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark fs-14 mb-2"><i class="ti ti-align-left text-primary me-1"></i>Work Description & Activities</h6>
                    <div class="p-3 bg-light rounded border text-dark fs-13 lh-base" id="modalDescription" style="white-space: pre-wrap; max-height: 280px; overflow-y: auto;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after_scripts')
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>

    <script>
        $(document).ready(function() {
            flatpickr("#startDate", {
                dateFormat: "d-m-Y"
            });

            flatpickr("#endDate", {
                dateFormat: "d-m-Y"
            });

            let isSearchClicked = false;

            // Builds the per-day header row (replaces the RowGroup extension)
            function buildGroupRow(rows, group) {
                        let minutes = 0;

                        rows.forEach(function(rowData) {
                            let total = rowData.TotalHours;
                            if (total) {
                                let t = total.split(':');
                                if (t.length === 2) {
                                    minutes += (parseInt(t[0]) * 60) + parseInt(t[1]);
                                }
                            }
                        });

                        let hh = Math.floor(minutes / 60);
                        let mm = minutes % 60;
                        let totalHours = String(hh).padStart(2, '0') + ":" + String(mm).padStart(2, '0');

                        let dayType = rows[0].day_type;
                        let filledDate = rows[0].CreationDate || '-';

                        if (dayType === "saturday") {
                            return $('<tr class="day-group-header day-group-saturday fw-semibold"/>')
                                .append(
                                    '<td colspan="6">' +
                                    '<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">' +
                                    '<span><i class="ti ti-calendar-event me-1 text-success"></i>' +
                                    '<strong>' + group + ' (Saturday)</strong>' +
                                    '<span class="badge bg-white text-success border ms-2 fs-11">Submitted: ' + filledDate + '</span>' +
                                    '</span>' +
                                    '<span class="badge bg-success text-white px-2.5 py-1 fs-12">Day Total: ' + totalHours + ' hrs</span>' +
                                    '</div>' +
                                    '</td>'
                                );
                        }

                        if (dayType === "sunday") {
                            return $('<tr class="day-group-header day-group-sunday fw-semibold"/>')
                                .append(
                                    '<td colspan="6">' +
                                    '<div class="d-flex align-items-center justify-content-between">' +
                                    '<span><i class="ti ti-sun me-1 text-danger"></i>' +
                                    '<strong>' + group + ' (Sunday)</strong>' +
                                    '</span>' +
                                    '<span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-11">Weekly Off</span>' +
                                    '</div>' +
                                    '</td>'
                                );
                        }

                        return $('<tr class="day-group-header day-group-normal fw-semibold"/>')
                            .append(
                                '<td colspan="6">' +
                                '<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">' +
                                '<span><i class="ti ti-calendar me-1 text-primary"></i>' +
                                '<strong>' + group + '</strong>' +
                                '<span class="badge bg-white text-secondary border ms-2 fs-11">Submitted: ' + filledDate + '</span>' +
                                '</span>' +
                                '<span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 fs-12">Day Total: ' + totalHours + ' hrs</span>' +
                                '</div>' +
                                '</td>'
                            );
                    }


            function isFilterEmpty() {
                return !$('#employeeFilter').val();
            }

            let table = $('#dailyReportList').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ordering: false,
                paging: true,
                pageLength: 1,
                lengthMenu: [[1], [1]],
                lengthChange: false,
                deferLoading: 0,
                layout: {
                    topStart: null,
                    topEnd: null,
                    bottomStart: 'info',
                    bottomEnd: 'paging'
                },
                language: {
                    info: "Viewing Month _START_ of _TOTAL_ in selected range",
                    infoEmpty: "",
                    emptyTable: "No daily reports found for this period."
                },
                ajax: {
                    url: "{{ route('employee-report.list') }}",
                    data: function(d) {
                        d.employee_id = $('#employeeFilter').val();
                        d.start_date = $('#startDate').val();
                        d.end_date = $('#endDate').val();
                        d.page = table.page.info().page;
                    },
                    dataSrc: function(json) {
                        if (!isSearchClicked) {
                            $('#totalHours').text('00:00');
                            $('#summaryKpiRow').addClass('d-none');
                            return [];
                        }

                        let totalMinutes = 0;
                        let daysLogged = 0;
                        let daysMissing = 0;

                        json.data.forEach(function(row) {
                            if (row.TotalHours) {
                                let parts = row.TotalHours.split(':');
                                if (parts.length === 2) {
                                    totalMinutes += (parseInt(parts[0]) * 60) + parseInt(parts[1]);
                                }
                                daysLogged++;
                            } else if (row.WorkDescription === 'Not Filled') {
                                daysMissing++;
                            }
                        });

                        let h = Math.floor(totalMinutes / 60);
                        let m = totalMinutes % 60;
                        let formattedTotal = String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');

                        $('#totalHours').text(formattedTotal + ' hrs');
                        $('#kpiTotalHours').text(formattedTotal + ' hrs');
                        $('#kpiDaysLogged').text(daysLogged);
                        $('#kpiDaysMissing').text(daysMissing);
                        $('#summaryKpiRow').removeClass('d-none');

                        return json.data;
                    }
                },
                rowCallback: function(row, data) {
                    if (data.day_type === "sunday") {
                        $(row).hide();
                        return;
                    }
                },
                columns: [
                    {
                        data: 'project_name',
                        render: function(data, type, row) {
                            if (row.WorkDescription === 'Not Filled') {
                                return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="ti ti-alert-triangle me-1"></i>Not Submitted</span>';
                            }
                            return `
                                <div class="d-flex align-items-center gap-1.5">
                                    <span class="avatar avatar-xs bg-light text-primary rounded d-inline-flex align-items-center justify-content-center">
                                        <i class="ti ti-folder fs-12"></i>
                                    </span>
                                    <span class="fw-semibold text-dark fs-13">${data || 'General Work'}</span>
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'WorkDescription',
                        render: function(data, type, row) {
                            if (!data || data === 'Not Filled') {
                                return '<span class="text-muted fs-12 fst-italic">No daily log submitted for this day.</span>';
                            }

                            return `<div class="work-text">${data}</div>`;
                        }
                    },
                    {
                        data: 'StartingTime',
                        render: data => data ? `<span class="fs-12 text-muted"><i class="ti ti-clock-play me-1 text-info"></i>${data}</span>` : '-'
                    },
                    {
                        data: 'EndingTime',
                        render: data => data ? `<span class="fs-12 text-muted"><i class="ti ti-clock-stop me-1 text-danger"></i>${data}</span>` : '-'
                    },
                    {
                        data: 'TotalHours',
                        className: 'text-end',
                        render: data => data ? `<span class="badge bg-light text-dark border fs-12 font-monospace">${data} hrs</span>` : '-'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            if (!row.WorkDescription || row.WorkDescription === 'Not Filled') {
                                return '-';
                            }
                            return `
                                <button type="button" class="btn btn-view-report" title="View"
                                    data-description="${encodeURIComponent(row.WorkDescription)}"
                                    data-project="${row.project_name || 'General'}"
                                    data-report="${row.DrEntryDate}"
                                    data-filled="${row.CreationDate || '-'}"
                                    data-start="${row.StartingTime || '-'}"
                                    data-end="${row.EndingTime || '-'}"
                                    data-hours="${row.TotalHours || '-'}">
                                    <i class="ti ti-eye"></i>
                                </button>
                            `;
                        }
                    }
                ],
                drawCallback: function() {
                    if (!isSearchClicked) {
                        $('#dailyReportList tbody').html(`
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="ti ti-filter-off fs-40 text-muted d-block mb-2"></i>
                                    <h6>Please select an employee and click <strong>Generate</strong></h6>
                                    <p class="fs-12 text-muted mb-0">Daily activity logs will populate here grouped by date.</p>
                                </td>
                            </tr>
                        `);
                        return;
                    }

                    const api = this.api();
                    const pageData = api.rows({ page: 'current' }).data().toArray();
                    const nodes = api.rows({ page: 'current' }).nodes().toArray();
                    let lastGroup = null;
                    pageData.forEach(function(rowData, i) {
                        if (rowData.group_date !== lastGroup) {
                            lastGroup = rowData.group_date;
                            const groupRows = pageData.filter(r => r.group_date === lastGroup);
                            $(nodes[i]).before(buildGroupRow(groupRows, lastGroup));
                        }
                    });
                }
            });

            $('#searchBtn').click(function() {
                if (isFilterEmpty()) {
                    alert('Please select an employee to generate daily reports.');
                    return;
                }

                isSearchClicked = true;
                const empName = $('#employeeFilter option:selected').text();
                $('#tableHeaderTitle').text('Daily Work Log: ' + empName);
                table.ajax.reload();
            });

            $('#exportBtn').click(function() {
                if (isFilterEmpty()) {
                    alert('Please select an employee to export daily reports.');
                    return;
                }

                const params = $.param({
                    employee_id: $('#employeeFilter').val(),
                    start_date: $('#startDate').val(),
                    end_date: $('#endDate').val()
                });

                window.location.href = "{{ route('employee-report.export') }}" + "?" + params;
            });

            $(document).on('click', '.btn-view-report', function() {
                $('#modalReportDate').text($(this).data('report'));
                $('#modalFilledDate').text($(this).data('filled'));
                $('#modalProject').text($(this).data('project'));
                $('#modalStartTime').text($(this).data('start'));
                $('#modalEndTime').text($(this).data('end'));
                $('#modalHours').text($(this).data('hours') + ' hrs');
                $('#modalDescription').text(decodeURIComponent($(this).data('description')));

                const modal = new bootstrap.Modal(document.getElementById('reportModal'));
                modal.show();
            });
        });
    </script>
@endpush

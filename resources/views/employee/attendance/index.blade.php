@extends('employee.layout.layout')
@section('title', $title)
@section('main-section')

    <div class="page-wrapper" style="min-height: 388px;">
        <div class="content">

            <!-- Breadcrumb -->
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Employee Attendance</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ti ti-smart-home"></i></a>
                            </li>
                            <li class="breadcrumb-item">
                                Employee
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Employee Attendance</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                    <div class="me-2 mb-2">
                        <div class="d-flex align-items-center border bg-white rounded p-1 me-2 icon-list">
                            <a href="{{ route('employee.attendance') }}" class="btn btn-icon btn-sm active bg-primary text-white me-1"><i
                                    class="ti ti-brand-days-counter"></i></a>
                            <a href="{{ route('employee.attendance.register') }}" class="btn btn-icon btn-sm"><i
                                    class="ti ti-calendar-event"></i></a>
                        </div>
                    </div>
                    <div class="me-2 mb-2">
                        <a href="{{ route('employee.attendance.export') }}" id="exportCsv"
                            class="btn btn-white d-inline-flex align-items-center">
                            <i class="ti ti-file-export me-1"></i>Export CSV
                        </a>
                    </div>
                    <div class="ms-2 head-icons">
                        <a href="javascript:void(0);" class="" data-bs-toggle="tooltip" data-bs-placement="top"
                            data-bs-original-title="Collapse" id="collapse-header">
                            <i class="ti ti-chevrons-up"></i>
                        </a>
                    </div>
                </div>
            </div>
            <!-- /Breadcrumb -->

            <div class="row">
                <div class="col-xl-3 col-lg-4 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body">
                            <div class="mb-3 text-center">
                                <h6 class="fw-medium text-gray-5 mb-1">
                                    Good {{ $now->hour < 12 ? 'Morning' : ($now->hour < 17 ? 'Afternoon' : 'Evening') }}, {{ $employee->employee_name ?? 'Employee' }}
                                </h6>
                                <h5 class="text-dark fw-bold mb-0">{{ $now->format('h:i A, d M Y') }}</h5>
                                <div class="text-muted fs-11 mt-1">Shift: {{ $shift->shift_name ?? 'General Shift' }} ({{ date('h:i A', strtotime($shift->start_time ?? '09:30')) }} - {{ date('h:i A', strtotime($shift->end_time ?? '18:30')) }})</div>
                            </div>
                            <div class="attendance-circle-progress mx-auto mb-3" data-value="100">
                                <div class="avatar avatar-xxl avatar-rounded bg-primary-subtle text-primary d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px; font-size: 28px; font-weight: 700;">
                                    {{ strtoupper(substr($employee->employee_name ?? 'E', 0, 2)) }}
                                </div>
                            </div>
                            <div class="text-center">
                                @if ($todayAttendance && $todayAttendance->check_in_at)
                                    <div class="badge badge-md bg-success-subtle text-success border border-success-subtle mb-3">
                                        Today: {{ $todayHours ?? 0 }} hrs
                                    </div>
                                    <h6 class="fw-medium d-flex align-items-center justify-content-center mb-3 text-dark fs-13">
                                        <i class="ti ti-clock-check text-success me-1"></i>
                                        Punched In at {{ \Carbon\Carbon::parse($todayAttendance->check_in_at)->format('h:i A') }}
                                    </h6>
                                    @if ($todayAttendance->check_out_at)
                                        <div class="alert alert-light border text-muted fs-12 py-2 mb-0">
                                            <i class="ti ti-circle-check text-success me-1"></i> Checked Out at {{ \Carbon\Carbon::parse($todayAttendance->check_out_at)->format('h:i A') }}
                                        </div>
                                    @else
                                        <form method="POST" action="{{ route('employee.attendance.punch') }}">
                                            @csrf
                                            <button type="submit" class="btn btn-dark w-100 d-flex align-items-center justify-content-center gap-1 shadow-sm">
                                                <i class="ti ti-logout fs-16"></i> Punch Out
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <div class="badge badge-md bg-warning-subtle text-warning border border-warning-subtle mb-3">
                                        Not Marked Today
                                    </div>
                                    <form method="POST" action="{{ route('employee.attendance.punch') }}">
                                        @csrf
                                        <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-1 shadow-sm">
                                            <i class="ti ti-fingerprint fs-18"></i> Punch In
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9 col-lg-8 d-flex">
                    <div class="row flex-fill">
                        @php
                            $fmtChange = function ($v) {
                                $up = $v >= 0;
                                return [$up ? 'bg-success' : 'bg-danger', $up ? 'ti-arrow-up' : 'ti-arrow-down', abs($v)];
                            };
                            [$wBg, $wIcon, $wPct] = $fmtChange($stats['week']['change']);
                            [$mBg, $mIcon, $mPct] = $fmtChange($stats['month']['change']);
                            [$oBg, $oIcon, $oPct] = $fmtChange($stats['overtime']['change']);
                        @endphp
                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="border-bottom mb-2 pb-2">
                                        <span class="avatar avatar-sm bg-primary mb-2"><i class="ti ti-clock-stop"></i></span>
                                        <h2 class="mb-2">{{ rtrim(rtrim(number_format($stats['today']['value'], 2), '0'), '.') ?: '0' }} / <span class="fs-20 text-gray-5">{{ rtrim(rtrim(number_format($stats['today']['target'], 1), '0'), '.') }}</span></h2>
                                        <p class="fw-medium text-truncate">Total Hours Today</p>
                                    </div>
                                    <div>
                                        <p class="d-flex align-items-center fs-13">
                                            <span class="avatar avatar-xs rounded-circle {{ $todayAttendance && !$todayAttendance->check_out_at && $todayAttendance->check_in_at ? 'bg-info' : 'bg-secondary' }} flex-shrink-0 me-2">
                                                <i class="ti ti-clock fs-12"></i>
                                            </span>
                                            <span>
                                                @if ($todayAttendance && $todayAttendance->check_in_at)
                                                    {{ $todayAttendance->check_out_at ? 'Day completed' : 'In progress' }}
                                                @else
                                                    Not punched in
                                                @endif
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="border-bottom mb-2 pb-2">
                                        <span class="avatar avatar-sm bg-dark mb-2"><i class="ti ti-clock-up"></i></span>
                                        <h2 class="mb-2">{{ rtrim(rtrim(number_format($stats['week']['value'], 1), '0'), '.') ?: '0' }} / <span class="fs-20 text-gray-5">{{ rtrim(rtrim(number_format($stats['week']['target'], 1), '0'), '.') }}</span></h2>
                                        <p class="fw-medium text-truncate">Total Hours Week</p>
                                    </div>
                                    <div>
                                        <p class="d-flex align-items-center fs-13">
                                            <span class="avatar avatar-xs rounded-circle {{ $wBg }} flex-shrink-0 me-2"><i class="ti {{ $wIcon }} fs-12"></i></span>
                                            <span>{{ $wPct }}% Last Week</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="border-bottom mb-2 pb-2">
                                        <span class="avatar avatar-sm bg-info mb-2"><i class="ti ti-calendar-up"></i></span>
                                        <h2 class="mb-2">{{ rtrim(rtrim(number_format($stats['month']['value'], 1), '0'), '.') ?: '0' }} / <span class="fs-20 text-gray-5">{{ rtrim(rtrim(number_format($stats['month']['target'], 1), '0'), '.') }}</span></h2>
                                        <p class="fw-medium text-truncate">Total Hours Month</p>
                                    </div>
                                    <div>
                                        <p class="d-flex align-items-center fs-13 text-truncate">
                                            <span class="avatar avatar-xs rounded-circle {{ $mBg }} flex-shrink-0 me-2"><i class="ti {{ $mIcon }} fs-12"></i></span>
                                            <span>{{ $mPct }}% Last Month</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="border-bottom mb-2 pb-2">
                                        <span class="avatar avatar-sm bg-pink mb-2"><i class="ti ti-calendar-star"></i></span>
                                        <h2 class="mb-2">{{ rtrim(rtrim(number_format($stats['overtime']['value'], 1), '0'), '.') ?: '0' }} <span class="fs-20 text-gray-5">hrs</span></h2>
                                        <p class="fw-medium text-truncate">Overtime this Month</p>
                                    </div>
                                    <div>
                                        <p class="d-flex align-items-center fs-13 text-truncate">
                                            <span class="avatar avatar-xs rounded-circle {{ $oBg }} flex-shrink-0 me-2"><i class="ti {{ $oIcon }} fs-12"></i></span>
                                            <span>{{ $oPct }}% Last Month</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-2 mb-3">
                                        <h5 class="mb-0">Monthly Overview <span class="text-muted fs-13 fw-normal" id="mcLabel"></span></h5>
                                        <input type="month" id="chartMonth" class="form-control w-auto" max="{{ $now->format('Y-m') }}" value="{{ $now->format('Y-m') }}">
                                    </div>
                                    <div class="row">
                                        <div class="col-xl-3 col-6"><div class="mb-3"><p class="d-flex align-items-center mb-1"><i class="ti ti-point-filled text-dark-transparent me-1"></i>Total Working hours</p><h3 id="mcTotal">-</h3></div></div>
                                        <div class="col-xl-3 col-6"><div class="mb-3"><p class="d-flex align-items-center mb-1"><i class="ti ti-point-filled text-success me-1"></i>Productive Hours</p><h3 id="mcProductive">-</h3></div></div>
                                        <div class="col-xl-3 col-6"><div class="mb-3"><p class="d-flex align-items-center mb-1"><i class="ti ti-point-filled text-warning me-1"></i>Break hours</p><h3 id="mcBreak">-</h3></div></div>
                                        <div class="col-xl-3 col-6"><div class="mb-3"><p class="d-flex align-items-center mb-1"><i class="ti ti-point-filled text-info me-1"></i>Overtime</p><h3 id="mcOvertime">-</h3></div></div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-3 fs-12 text-muted mb-2" id="mcSummary"></div>
                                    <div class="position-relative" style="height:150px;">
                                        <div id="mcTargetLine" class="position-absolute w-100 border-top border-dashed" style="border-top:1px dashed #adb5bd;z-index:1;pointer-events:none;"></div>
                                        <div id="mcBars" class="d-flex align-items-end h-100" style="gap:3px;"></div>
                                    </div>
                                    <div id="mcDays" class="d-flex" style="gap:3px;"></div>
                                    <div class="d-flex flex-wrap gap-3 fs-11 mt-3">
                                        <span><i class="ti ti-square-filled text-success"></i> Full day</span>
                                        <span><i class="ti ti-square-filled text-warning"></i> Short day</span>
                                        <span><i class="ti ti-square-filled text-danger"></i> Absent</span>
                                        <span><i class="ti ti-square-filled text-info"></i> Holiday</span>
                                        <span><i class="ti ti-square-filled text-secondary"></i> Week off</span>
                                        <span class="text-muted">- - - shift target (<span id="mcTarget">9</span>h)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5>Employee Attendance</h5>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                        <div class="me-3">
                            <div class="input-icon-end position-relative">
                                <input type="text" id="attendanceRange" class="form-control date-range"
                                    placeholder="dd/mm/yyyy - dd/mm/yyyy" autocomplete="off"
                                    value="{{ \Carbon\Carbon::parse($filters['from'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filters['to'])->format('d/m/Y') }}">
                                <span class="input-icon-addon"><i class="ti ti-chevron-down"></i></span>
                            </div>
                        </div>
                        <div>
                            <select id="statusFilter" class="form-select">
                                <option value="">All Status</option>
                                @foreach (['Present', 'Absent', 'Week Off', 'Holiday'] as $st)
                                    <option value="{{ $st }}">{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table w-100" id="attendanceTable">
                        <thead class="thead-light">
                            <tr>
                                <th>Date</th>
                                <th>Check In</th>
                                <th>Status</th>
                                <th>Check Out</th>
                                <th>Break</th>
                                <th>Late</th>
                                <th>Overtime</th>
                                <th>Production Hours</th>
                                <th></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

        </div>

        <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
            <p class="mb-0">2014 - 2025 © EMS.</p>
            <p>Designed &amp; Developed By <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
        </div>

    </div>

    <div class="modal fade" id="attendance_report">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Attendance</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="card shadow-none bg-transparent-light">
                        <div class="card-body pb-1">
                            <div class="row">
                                <div class="col-sm-3"><div class="mb-3"><span>Date</span><p class="text-gray-9 fw-medium" id="m-date">-</p></div></div>
                                <div class="col-sm-3"><div class="mb-3"><span>Punch in at</span><p class="text-gray-9 fw-medium" id="m-in">-</p></div></div>
                                <div class="col-sm-3"><div class="mb-3"><span>Punch out at</span><p class="text-gray-9 fw-medium" id="m-out">-</p></div></div>
                                <div class="col-sm-3"><div class="mb-3"><span>Status</span><p class="text-gray-9 fw-medium" id="m-status">-</p></div></div>
                            </div>
                        </div>
                    </div>
                    <div class="card shadow-none border mb-0">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-xl-3"><div class="mb-2"><p class="mb-1"><i class="ti ti-point-filled text-success me-1"></i>Production Hours</p><h3 id="m-hours">-</h3></div></div>
                                <div class="col-xl-3"><div class="mb-2"><p class="mb-1"><i class="ti ti-point-filled text-warning me-1"></i>Break</p><h3 id="m-break">-</h3></div></div>
                                <div class="col-xl-3"><div class="mb-2"><p class="mb-1"><i class="ti ti-point-filled text-danger me-1"></i>Late</p><h3 id="m-late">-</h3></div></div>
                                <div class="col-xl-3"><div class="mb-2"><p class="mb-1"><i class="ti ti-point-filled text-info me-1"></i>Overtime</p><h3 id="m-ot">-</h3></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after_scripts')
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script>
        $(function () {
            var from = '{{ $filters['from'] }}';
            var to = '{{ $filters['to'] }}';
            var exportBase = "{{ route('employee.attendance.export') }}";

            function syncExport() {
                var q = $.param({from: from, to: to, status: $('#statusFilter').val()});
                $('#exportCsv').attr('href', exportBase + '?' + q);
            }
            syncExport();

            // ---- Monthly overview chart (ajax) ----
            var monthUrl = "{{ route('employee.attendance.month-chart') }}";
            function loadMonth(month) {
                $.getJSON(monthUrl, {month: month}, function (res) {
                    $('#mcLabel').text('- ' + res.label);
                    $('#mcTotal').text(res.totals.total);
                    $('#mcProductive').text(res.totals.productive);
                    $('#mcBreak').text(res.totals.break);
                    $('#mcOvertime').text(res.totals.overtime);
                    $('#mcTarget').text(res.target);
                    $('#mcSummary').html('<span>Present: <b>' + res.totals.present + '</b></span>' +
                        '<span>Absent: <b>' + res.totals.absent + '</b></span>' +
                        '<span>Late days: <b>' + res.totals.late + '</b></span>');

                    var maxH = Math.max(res.target * 1.25, Math.max.apply(null, res.days.map(function (d) { return d.hours; })));
                    $('#mcTargetLine').css('bottom', (res.target / maxH * 100) + '%');

                    var bars = '', labels = '';
                    res.days.forEach(function (d) {
                        var color, h;
                        if (d.status === 'Present') {
                            color = d.hours >= res.target ? '#03C95A' : '#FFC107';
                            h = Math.max(3, d.hours / maxH * 100);
                        } else if (d.status === 'Absent') {
                            color = '#E70D0D'; h = 6;
                        } else if (d.status === 'Holiday') {
                            color = '#0dcaf0'; h = 6;
                        } else if (d.status === 'Week Off') {
                            color = '#adb5bd'; h = 6;
                        } else {
                            color = 'transparent'; h = 0; // future day
                        }
                        bars += '<div class="flex-fill d-flex align-items-end" style="min-width:0;height:100%;" title="' + $('<i>').text(d.tip).html() + '">' +
                                '<div class="w-100 rounded-top" style="height:' + h + '%;background:' + color + ';"></div></div>';
                        labels += '<div class="flex-fill text-center fs-10 text-muted" style="min-width:0;">' + d.day + '</div>';
                    });
                    $('#mcBars').html(bars);
                    $('#mcDays').html(labels);
                });
            }
            loadMonth($('#chartMonth').val());
            $('#chartMonth').on('change', function () { if (this.value) loadMonth(this.value); });

            var table = $('#attendanceTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                scrollX: true,
                order: [[0, 'desc']],
                ajax: {
                    url: "{{ route('employee.attendance.list') }}",
                    data: function (d) {
                        d.from = from;
                        d.to = to;
                        d.status = $('#statusFilter').val();
                    }
                },
                columns: [
                    {data: 'date'},
                    {data: 'check_in', orderable: false},
                    {data: 'status'},
                    {data: 'check_out', orderable: false},
                    {data: 'break'},
                    {data: 'late'},
                    {data: 'overtime'},
                    {data: 'hours'},
                    {data: 'action', orderable: false, searchable: false}
                ],
                language: {emptyTable: 'No attendance records found for this period'},
                dom: "<'row mb-2'<'col-md-6'l><'col-md-6 text-end'f>>" +
                     "<'row'<'col-md-12'tr>>" +
                     "<'row mt-2'<'col-md-5'i><'col-md-7'p>>"
            });

            $('#statusFilter').on('change', function () {
                syncExport();
                table.ajax.reload();
            });

            // Fill the detail modal from the clicked row (rows are created by ajax, so delegate)
            $('#attendanceTable').on('click', '.view-attendance', function () {
                var d = this.dataset;
                var map = {'m-date': 'date', 'm-in': 'in', 'm-out': 'out', 'm-status': 'status',
                           'm-hours': 'hours', 'm-break': 'break', 'm-late': 'late', 'm-ot': 'ot'};
                Object.keys(map).forEach(function (id) {
                    document.getElementById(id).textContent = d[map[id]] || '-';
                });
            });

            if ($.fn.daterangepicker) {
                var $r = $('#attendanceRange');
                $r.daterangepicker({
                    autoUpdateInput: false,
                    startDate: moment(from),
                    endDate: moment(to),
                    maxDate: moment(),
                    locale: {format: 'DD/MM/YYYY'},
                    ranges: {
                        'Today': [moment(), moment()],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment()],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                    }
                });
                $r.on('apply.daterangepicker', function (ev, picker) {
                    from = picker.startDate.format('YYYY-MM-DD');
                    to = picker.endDate.format('YYYY-MM-DD');
                    $r.val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
                    syncExport();
                    table.ajax.reload();
                });
            }
        });
    </script>
@endpush

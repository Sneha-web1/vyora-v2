<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resignation Notification</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px; color: #1f2228; }
        .container { max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; }
        .header { background: #241f4b; color: #ffffff; padding: 24px 30px; }
        .header h2 { margin: 0 0 6px; font-size: 20px; font-weight: 700; }
        .header p { margin: 0; opacity: 0.9; font-size: 13px; }
        .content { padding: 30px; }
        .alert-pill { display: inline-block; padding: 6px 14px; background: #fff6dc; color: #a37400; border-radius: 8px; font-size: 12px; font-weight: 700; margin-bottom: 20px; border: 1px solid #ffe9a8; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .meta-table td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; font-size: 13px; vertical-align: top; }
        .meta-table td.label { width: 38%; color: #6b7280; font-weight: 600; }
        .meta-table td.val { color: #111827; font-weight: 600; }
        .reason-box { background: #f8f9fa; border-left: 4px solid #332c6d; padding: 16px; border-radius: 6px; margin-bottom: 24px; }
        .reason-box h4 { margin: 0 0 8px; font-size: 13px; color: #374151; text-transform: uppercase; letter-spacing: 0.5px; }
        .reason-box p { margin: 0; font-size: 13px; line-height: 1.6; color: #4b5563; white-space: pre-line; }
        .btn-group { margin: 28px 0 10px; text-align: center; }
        .btn { display: inline-block; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 13px; margin: 0 6px 8px; }
        .btn-accept { background: #03c95a; color: #ffffff !important; }
        .btn-reject { background: #e70d0d; color: #ffffff !important; }
        .btn-view { background: #111827; color: #ffffff !important; }
        .footer { background: #f8f9fa; padding: 20px 30px; text-align: center; font-size: 11px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
        .cc-list { background: #f3f4f6; padding: 8px 12px; border-radius: 6px; font-size: 12px; color: #4b5563; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{{ $companyDetails->company_name ?? 'Neural Info Solutions' }}</h2>
            <p>Employee Resignation & Exit Notification</p>
        </div>
        <div class="content">
            <div class="alert-pill">
                @if ($resignation->initiated_by === 'employee')
                    🔔 Resignation Submitted by Employee
                @else
                    ⚠️ Exit / Separation Initiated by Supervisor / Admin
                @endif
            </div>

            <p style="font-size: 14px; line-height: 1.6; margin-top: 0;">
                Hello,
            </p>
            <p style="font-size: 14px; line-height: 1.6; color: #374151;">
                This is a formal notification that an employee resignation/separation request has been submitted in the HR system with the following particulars:
            </p>

            <table class="meta-table">
                <tr>
                    <td class="label">Reference ID</td>
                    <td class="val"><strong style="color: #332c6d;">{{ $resignation->resignation_code }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Employee Name</td>
                    <td class="val">{{ $employee->employee_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Employee Email</td>
                    <td class="val">{{ $employee->employee_email ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Designation</td>
                    <td class="val">{{ $employee->designation->name ?? ($employee->designation->designation_name ?? 'Staff') }}</td>
                </tr>
                <tr>
                    <td class="label">Reporting Supervisor</td>
                    <td class="val">{{ $supervisor->employee_name ?? 'Unassigned' }}</td>
                </tr>
                <tr>
                    <td class="label">Submission Date</td>
                    <td class="val">{{ \Carbon\Carbon::parse($resignation->resignation_date)->format('d M, Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Notice Period</td>
                    <td class="val">{{ $resignation->notice_period_days }} Days</td>
                </tr>
                <tr>
                    <td class="label">Requested Last Working Day</td>
                    <td class="val" style="color: #b30a0a;">{{ \Carbon\Carbon::parse($resignation->requested_last_working_day)->format('d M, Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Resignation Reason</td>
                    <td class="val">{{ $resignation->reason }}</td>
                </tr>
            </table>

            @if (!empty($resignation->reason_details))
                <div class="reason-box">
                    <h4>Resignation Letter / Statement:</h4>
                    <p>{{ $resignation->reason_details }}</p>
                </div>
            @endif

            @if (!empty($resignation->cc_emails) && count($resignation->clean_cc_emails) > 0)
                <div class="cc-list">
                    <strong>CC Recipients:</strong> {{ implode(', ', $resignation->clean_cc_emails) }}
                </div>
            @endif

            <p style="font-size: 13px; color: #6b7280; margin-top: 24px; text-align: center;">
                You can directly respond to this resignation below, or login to the HR portal:
            </p>

            <!-- Direct Response Buttons -->
            <div class="btn-group">
                <a href="{{ $acceptUrl }}" class="btn btn-accept" target="_blank">
                    ✓ Accept Resignation
                </a>
                <a href="{{ $rejectUrl }}" class="btn btn-reject" target="_blank">
                    ✕ Reject Resignation
                </a>
                <a href="{{ $viewUrl }}" class="btn btn-view" target="_blank">
                    View in Portal
                </a>
            </div>

            <p style="font-size: 11px; color: #9ca3af; text-align: center; margin-top: 15px;">
                Note: Clicking Accept or Reject above opens a secure confirmation window to finalize relieving dates and remarks.
            </p>
        </div>
        <div class="footer">
            <p style="margin: 0 0 4px;">{{ $companyDetails->company_name ?? 'VProtect EMS' }} &bull; Automated HR Notification</p>
            <p style="margin: 0;">Please do not reply directly to this automated email.</p>
        </div>
    </div>
</body>
</html>

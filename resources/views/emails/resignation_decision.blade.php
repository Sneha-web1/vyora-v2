<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resignation Decision Notification</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8f9fa; margin: 0; padding: 20px; color: #1f2228; }
        .container { max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; }
        .header { background: {{ $resignation->status === 'approved' ? '#028a3f' : '#8f0808' }}; color: #ffffff; padding: 24px 30px; }
        .header h2 { margin: 0 0 6px; font-size: 20px; font-weight: 700; }
        .header p { margin: 0; opacity: 0.9; font-size: 13px; }
        .content { padding: 30px; }
        .status-badge { display: inline-block; padding: 8px 18px; border-radius: 8px; font-size: 13px; font-weight: 700; margin-bottom: 20px; {{ $resignation->status === 'approved' ? 'background: #e6f9ee; color: #028a3f; border: 1px solid #b9efd0;' : 'background: #fde7e7; color: #8f0808; border: 1px solid #f9c2c2;' }} }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        .meta-table td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; font-size: 13px; vertical-align: top; }
        .meta-table td.label { width: 38%; color: #6b7280; font-weight: 600; }
        .meta-table td.val { color: #111827; font-weight: 600; }
        .remarks-box { background: #f8f9fa; border-left: 4px solid {{ $resignation->status === 'approved' ? '#03c95a' : '#e70d0d' }}; padding: 16px; border-radius: 6px; margin-bottom: 24px; }
        .remarks-box h4 { margin: 0 0 8px; font-size: 13px; color: #374151; text-transform: uppercase; }
        .remarks-box p { margin: 0; font-size: 13px; line-height: 1.6; color: #4b5563; white-space: pre-line; }
        .footer { background: #f8f9fa; padding: 20px 30px; text-align: center; font-size: 11px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{{ $companyDetails->company_name ?? 'Neural Info Solutions' }}</h2>
            <p>Resignation Decision Notification</p>
        </div>
        <div class="content">
            <div class="status-badge">
                @if ($resignation->status === 'approved')
                    ✓ Resignation Formally Accepted & Approved
                @else
                    ✕ Resignation Request Rejected / Declined
                @endif
            </div>

            <p style="font-size: 14px; line-height: 1.6; margin-top: 0;">
                Dear <strong>{{ $employee->employee_name ?? 'Employee' }}</strong>,
            </p>
            <p style="font-size: 14px; line-height: 1.6; color: #374151;">
                @if ($resignation->status === 'approved')
                    Your resignation request (Ref: <strong>{{ $resignation->resignation_code }}</strong>) has been reviewed and formally <strong>accepted</strong> by management.
                @else
                    Your resignation request (Ref: <strong>{{ $resignation->resignation_code }}</strong>) has been reviewed by management and has been <strong>rejected / retained</strong>.
                @endif
            </p>

            <table class="meta-table">
                <tr>
                    <td class="label">Reference ID</td>
                    <td class="val">{{ $resignation->resignation_code }}</td>
                </tr>
                <tr>
                    <td class="label">Reviewed By</td>
                    <td class="val">{{ $resignation->action_by_name ?? 'Management / HR' }} ({{ ucfirst($resignation->action_by_type ?? 'HR') }})</td>
                </tr>
                <tr>
                    <td class="label">Decision Date</td>
                    <td class="val">{{ $resignation->action_at ? \Carbon\Carbon::parse($resignation->action_at)->format('d M, Y h:i A') : now()->format('d M, Y') }}</td>
                </tr>
                @if ($resignation->status === 'approved')
                    <tr>
                        <td class="label">Approved Last Working Day</td>
                        <td class="val" style="color: #03c95a; font-size: 15px;">
                            <strong>{{ \Carbon\Carbon::parse($resignation->effectiveLastWorkingDay())->format('d M, Y') }}</strong>
                        </td>
                    </tr>
                @endif
            </table>

            @if (!empty($resignation->action_remarks))
                <div class="remarks-box">
                    <h4>Reviewer Remarks / Exit Instructions:</h4>
                    <p>{{ $resignation->action_remarks }}</p>
                </div>
            @endif

            @if ($resignation->status === 'approved')
                <div style="background: #f0fcf5; border: 1px solid #b9efd0; border-radius: 8px; padding: 14px 18px; margin-top: 20px;">
                    <h5 style="margin: 0 0 6px; color: #028a3f; font-size: 13px; font-weight: 700;">Handover & Relieving Guidelines:</h5>
                    <ul style="margin: 0; padding-left: 20px; font-size: 12px; color: #028a3f; line-height: 1.6;">
                        <li>Ensure comprehensive handover of active tasks, files, and credentials to your supervisor.</li>
                        <li>Return all company assets, ID cards, and access keys on or before your last working day.</li>
                        <li>Your final settlement (Full & Final) will be computed post completion of all clearance sign-offs.</li>
                    </ul>
                </div>
            @endif
        </div>
        <div class="footer">
            <p style="margin: 0 0 4px;">{{ $companyDetails->company_name ?? 'VProtect EMS' }} &bull; Human Resources Department</p>
            <p style="margin: 0;">Automated message generated by Chitragupta HRMS.</p>
        </div>
    </div>
</body>
</html>

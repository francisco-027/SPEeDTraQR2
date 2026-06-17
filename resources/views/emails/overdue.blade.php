<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; color: #1a1a1a; margin: 0; padding: 0; background: #f5f5f5; }
        .wrapper { max-width: 580px; margin: 32px auto; background: #fff; border-radius: 8px; overflow: hidden; border: 1px solid #e0e0e0; }
        .header { background: #9333ea; padding: 24px 32px; }
        .header h1 { color: #fff; margin: 0; font-size: 20px; }
        .body { padding: 28px 32px; }
        .alert-box { background: #faf5ff; border: 1px solid #d8b4fe; border-radius: 6px; padding: 16px 20px; margin-bottom: 24px; }
        .alert-box p { margin: 0; font-size: 14px; color: #6b21a8; }
        .detail-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .detail-table td { padding: 8px 0; border-bottom: 1px solid #f0f0f0; }
        .detail-table td:first-child { color: #666; width: 40%; }
        .detail-table td:last-child { font-weight: 600; }
        .mitigation { margin-top: 24px; }
        .mitigation h3 { font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em; color: #555; margin-bottom: 12px; }
        .mitigation ul { margin: 0; padding-left: 20px; font-size: 14px; line-height: 1.8; }
        .cta { margin-top: 28px; text-align: center; }
        .cta a { display: inline-block; background: #9333ea; color: #fff; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-size: 14px; font-weight: 600; }
        .footer { background: #f9f9f9; padding: 16px 32px; font-size: 12px; color: #999; border-top: 1px solid #e0e0e0; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>Document Overdue Alert — SPeED TraQR</h1>
    </div>
    <div class="body">
        <div class="alert-box">
            <p>
                Document <strong>{{ $document->tracking_number }}</strong> has been held in
                <strong>{{ $document->currentDepartment->name }}</strong> for more than
                <strong>{{ $document->currentDepartment->sla_hours }} hours</strong> and is now overdue.
                The citizen is waiting. Please take action immediately.
            </p>
        </div>

        <table class="detail-table">
            <tr>
                <td>Tracking Number</td>
                <td>{{ $document->tracking_number }}</td>
            </tr>
            <tr>
                <td>Document Type</td>
                <td>{{ $document->document_type }}</td>
            </tr>
            <tr>
                <td>Citizen Name</td>
                <td>{{ $document->citizen_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Department</td>
                <td>{{ $document->currentDepartment->name }}</td>
            </tr>
            <tr>
                <td>SLA Limit</td>
                <td>{{ $document->currentDepartment->sla_hours }} hours</td>
            </tr>
        </table>

        <div class="mitigation">
            <h3>Recommended Actions</h3>
            <ul>
                <li>Locate the physical document and assign it for immediate processing.</li>
                <li>Escalate to the department head if processing is blocked by an approval or resource constraint.</li>
                <li>Once processed, scan OUT the document so the timeline reflects the correct completion time.</li>
                <li>Log a reason for the delay in the system for audit and SLA reporting purposes.</li>
            </ul>
        </div>

        <div class="cta">
            <a href="{{ url('/track/' . $document->tracking_number) }}">View Document Timeline</a>
        </div>
    </div>
    <div class="footer">
        This is an automated overdue alert from SPeED TraQR. Do not reply to this email.<br>
        Tracking URL: {{ url('/track/' . $document->tracking_number) }}
    </div>
</div>
</body>
</html>

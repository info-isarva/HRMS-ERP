<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notice — {{ $complaint->case_number }}</title>
    <link rel="stylesheet" href="{{ asset('css/posh-theme.css') }}">
    <style>body{padding:40px;max-width:800px;margin:0 auto;} @media print{.no-print{display:none;}}</style>
</head>
<body>
    <p class="no-print"><button onclick="window.print()">Print notice</button> <a href="{{ route('cases.operate', $complaint) }}">Back</a></p>
    <h1>Notice to Respondent</h1>
    <p><strong>Case:</strong> {{ $complaint->case_number }}</p>
    <p><strong>Date:</strong> {{ now()->format('d M Y') }}</p>
    <p>To: <strong>{{ $complaint->respondent_name }}</strong> ({{ config('posh.respondent_types.'.$complaint->respondent_type) }})</p>
    <hr>
    <p>Under the Sexual Harassment of Women at Workplace (Prevention, Prohibition and Redressal) Act, 2013 and applicable Rules, you are hereby informed that a complaint has been received and is under inquiry by the Internal Committee.</p>
    <p><strong>Summary:</strong> A complaint alleging workplace sexual harassment has been filed. You are entitled to receive a copy of the complaint and at least <strong>seven working days</strong> notice before the hearing (Rule 6).</p>
    @if($complaint->getCaseData('hearing_date'))
        <p><strong>Proposed hearing date:</strong> {{ $complaint->getCaseData('hearing_date') }}</p>
    @endif
    @if($complaint->getCaseData('notice_date'))
        <p><strong>Notice issued on:</strong> {{ $complaint->getCaseData('notice_date') }}</p>
    @endif
    <p>You may submit a written reply and present evidence. Principles of natural justice apply (Section 11(3)).</p>
    <p style="margin-top:40px;">_________________________<br>Internal Committee<br>{{ $complaint->organization->display_name ?? '' }}</p>
</body>
</html>

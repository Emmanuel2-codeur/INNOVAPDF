@php
    $content = $doc->content ?? [];
    $style = $doc->style ?? [];
    $cert = $content['certificate'] ?? [];
    $primaryColor = $style['primaryColor'] ?? '#4f46e5';
    $fontFamily = $style['fontFamily'] ?? 'Helvetica, Arial, sans-serif';
    $issueDate = !empty($cert['issueDate']) ? \Carbon\Carbon::parse($cert['issueDate'])->translatedFormat('d F Y') : '____________';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $doc->title }}</title>
    <style>
        @page { margin: 30mm 25mm; }
        body { font-family: {{ $fontFamily }}; color: #1f2937; font-size: 12px; }
        h1 { text-align: center; text-transform: uppercase; letter-spacing: 2px; font-size: 20px; color: {{ $primaryColor }}; border-bottom: 2px solid {{ $primaryColor }}; padding-bottom: 16px; margin-bottom: 40px; }
        .purpose { text-align: center; text-transform: uppercase; font-size: 10px; color: #6b7280; margin-bottom: 30px; }
        .recipient { text-align: center; font-size: 15px; font-weight: bold; margin-bottom: 24px; }
        .body-text { text-align: justify; line-height: 1.8; white-space: pre-line; }
        .footer { margin-top: 60px; overflow: hidden; font-size: 11px; }
        .footer .date { float: left; color: #6b7280; }
        .footer .signature { float: right; text-align: right; }
        .footer .signature .name { font-weight: bold; }
        .footer .signature .title { color: #6b7280; font-size: 10px; }
    </style>
</head>
<body>
    <h1>{{ $doc->type === 'certificate' ? 'Certificat' : 'Attestation' }}</h1>

    @if(!empty($cert['purpose']))<p class="purpose">{{ $cert['purpose'] }}</p>@endif

    <p class="recipient">{{ $cert['recipientName'] ?? 'Nom du bénéficiaire' }}</p>

    <div class="body-text">{{ $cert['body'] ?? '' }}</div>

    <div class="footer">
        <p class="date">Fait le {{ $issueDate }}</p>
        <div class="signature">
            <p class="name">{{ $cert['issuerName'] ?? '' }}</p>
            <p class="title">{{ $cert['issuerTitle'] ?? '' }}</p>
        </div>
    </div>
</body>
</html>

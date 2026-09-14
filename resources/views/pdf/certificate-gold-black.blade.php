@php
    $content = $doc->content ?? [];
    $style = $doc->style ?? [];
    $cert = $content['certificate'] ?? [];
    $primaryColor = $style['primaryColor'] ?? '#eab308';
    $fontFamily = $style['fontFamily'] ?? 'Helvetica, Arial, sans-serif';
    $issueDate = !empty($cert['issueDate']) ? \Carbon\Carbon::parse($cert['issueDate'])->translatedFormat('d F Y') : '____________';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $doc->title }}</title>
    <style>
        @page { margin: 15mm 20mm; size: A4 landscape; }
        body { font-family: {{ $fontFamily }}; color: #1f2937; font-size: 12px; text-align: center; }
        .top-bar { height: 5px; background-color: {{ $primaryColor }}; margin: 0 -20mm 20px; }
        h1 { text-transform: uppercase; letter-spacing: 4px; font-size: 26px; margin: 0 0 4px; }
        .purpose { text-transform: uppercase; letter-spacing: 3px; font-size: 10px; color: #9ca3af; margin-bottom: 30px; }
        .presented { font-size: 11px; color: #6b7280; margin-bottom: 8px; }
        .recipient { font-size: 22px; font-style: italic; font-family: Georgia, serif; border-bottom: 1px solid {{ $primaryColor }}; display: inline-block; padding-bottom: 8px; margin-bottom: 30px; }
        .body-text { font-size: 10px; color: #6b7280; max-width: 420px; margin: 0 auto 40px; white-space: pre-line; }
        table.sign { width: 420px; margin: 0 auto; font-size: 10px; color: #6b7280; }
        table.sign td { border-top: 1px solid #d1d5db; padding-top: 6px; width: 50%; }
    </style>
</head>
<body>
    <div class="top-bar"></div>
    <h1>{{ $doc->type === 'certificate' ? 'CERTIFICATE' : 'ATTESTATION' }}</h1>
    <p class="purpose">{{ $cert['purpose'] ?? 'of appreciation' }}</p>
    <p class="presented">Proudly presented to</p>
    <p class="recipient">{{ $cert['recipientName'] ?? 'Nom du bénéficiaire' }}</p>
    <p class="body-text">{{ $cert['body'] ?? '' }}</p>
    <table class="sign">
        <tr>
            <td>{{ $issueDate }}<br><span style="font-size:8px;">Date</span></td>
            <td>{{ $cert['issuerName'] ?? '' }}<br><span style="font-size:8px;">{{ $cert['issuerTitle'] ?? 'Signature' }}</span></td>
        </tr>
    </table>
</body>
</html>

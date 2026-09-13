@php
    $content = $doc->content ?? [];
    $style = $doc->style ?? [];
    $profile = $content['profile'] ?? [];
    $letter = $content['coverLetter'] ?? [];
    $primaryColor = $style['primaryColor'] ?? '#4f46e5';
    $fontFamily = $style['fontFamily'] ?? 'Helvetica, Arial, sans-serif';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $doc->title }}</title>
    <style>
        @page { margin: 25mm 20mm; }
        body { font-family: {{ $fontFamily }}; color: #1f2937; font-size: 11px; line-height: 1.6; }
        .header-table { width: 100%; margin-bottom: 40px; }
        .header-table td { font-size: 10px; vertical-align: top; }
        .sender { color: #374151; }
        .sender .name { font-weight: bold; font-size: 12px; }
        .recipient { text-align: right; color: #374151; }
        .recipient .name { font-weight: bold; }
        .subject { font-weight: bold; margin-bottom: 24px; color: {{ $primaryColor }}; }
        .body-text { white-space: pre-line; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="sender" style="width: 50%;">
                <p class="name">{{ $profile['fullName'] ?? 'Nom Prénom' }}</p>
                @if(!empty($profile['email']))<p>{{ $profile['email'] }}</p>@endif
                @if(!empty($profile['phone']))<p>{{ $profile['phone'] }}</p>@endif
                @if(!empty($profile['location']))<p>{{ $profile['location'] }}</p>@endif
            </td>
            <td class="recipient" style="width: 50%;">
                <p class="name">{{ $letter['recipientName'] ?? 'Destinataire' }}</p>
                @if(!empty($letter['recipientCompany']))<p>{{ $letter['recipientCompany'] }}</p>@endif
            </td>
        </tr>
    </table>

    <p class="subject">Objet : {{ $letter['subject'] ?? 'Candidature' }}</p>

    <div class="body-text">{{ $letter['body'] ?? '' }}</div>
</body>
</html>

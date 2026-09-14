@php
    $content = $doc->content ?? [];
    $style = $doc->style ?? [];
    $profile = $content['profile'] ?? [];
    $letter = $content['coverLetter'] ?? [];
    $primaryColor = $style['primaryColor'] ?? '#3f6252';
    $fontFamily = $style['fontFamily'] ?? 'Helvetica, Arial, sans-serif';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $doc->title }}</title>
    <style>
        @page { margin: 0; }
        body { font-family: {{ $fontFamily }}; color: #1f2937; font-size: 11px; margin: 0; background-color: #faf3ea; }
        .band { background-color: {{ $primaryColor }}; color: #fff; padding: 14mm 20mm; overflow: hidden; }
        .band td { font-size: 10px; vertical-align: top; }
        .band .lbl { opacity: 0.7; font-size: 9px; margin-bottom: 2px; }
        .band .name { font-weight: bold; }
        .content { padding: 16mm 20mm; }
        h1 { font-size: 22px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 16px; }
        .badge { display: inline-block; background-color: #1f2937; color: #fff; padding: 5px 14px; border-radius: 4px; font-size: 10px; margin-bottom: 20px; }
        .body-text { white-space: pre-line; text-align: justify; line-height: 1.7; }
    </style>
</head>
<body>
    <table class="band" width="100%">
        <tr>
            <td style="width:50%;"><p class="lbl">Expéditeur :</p><p class="name">{{ $profile['fullName'] ?? 'Nom Prénom' }}</p>@if(!empty($profile['email']))<p>{{ $profile['email'] }}</p>@endif</td>
            <td style="width:50%; text-align:right;"><p class="lbl">Destinataire :</p><p class="name">{{ $letter['recipientName'] ?? 'Destinataire' }}</p><p>{{ $letter['recipientCompany'] ?? '' }}</p></td>
        </tr>
    </table>
    <div class="content">
        <h1>Lettre de motivation</h1>
        <span class="badge">{{ $letter['subject'] ?? 'Candidature' }}</span>
        <p>Madame, Monsieur,</p>
        <div class="body-text">{{ $letter['body'] ?? '' }}</div>
    </div>
</body>
</html>

@php
    $content = $doc->content ?? [];
    $style = $doc->style ?? [];
    $profile = $content['profile'] ?? [];
    $letter = $content['coverLetter'] ?? [];
    $fontFamily = $style['fontFamily'] ?? 'Helvetica, Arial, sans-serif';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $doc->title }}</title>
    <style>
        @page { margin: 15mm; background-color: #fde9c8; }
        body { font-family: {{ $fontFamily }}; color: #374151; font-size: 11px; line-height: 1.7; background-color: #fde9c8; }
        .card { background-color: rgba(255,255,255,0.92); border-radius: 24px; padding: 18mm; }
        .to { text-align: right; margin: 16px 0; }
        .subject { margin: 16px 0; }
        .body-text { white-space: pre-line; text-align: justify; }
    </style>
</head>
<body>
    <div class="card">
        <p>{{ $profile['fullName'] ?? '[Prénom & Nom]' }}</p>
        <p>{{ $profile['location'] ?? '[Adresse]' }}</p>
        @if(!empty($profile['phone']))<p>{{ $profile['phone'] }}</p>@endif
        @if(!empty($profile['email']))<p>{{ $profile['email'] }}</p>@endif

        <div class="to">
            À l'attention de<br>
            <strong>{{ $letter['recipientName'] ?? '[Nom du recruteur]' }}</strong><br>
            {{ $letter['recipientCompany'] ?? '' }}
        </div>

        <p class="subject"><strong>Objet :</strong> {{ $letter['subject'] ?? 'Candidature' }}</p>

        <p>Madame, Monsieur,</p>
        <div class="body-text">{{ $letter['body'] ?? '' }}</div>

        <p style="margin-top:20px;">{{ $profile['fullName'] ?? '' }}</p>
    </div>
</body>
</html>

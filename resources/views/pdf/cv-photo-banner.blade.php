@php
    $content = $doc->content ?? [];
    $style = $doc->style ?? [];
    $profile = $content['profile'] ?? [];
    $experiences = $content['experiences'] ?? [];
    $education = $content['education'] ?? [];
    $skills = $profile['skills'] ?? [];
    $primaryColor = $style['primaryColor'] ?? '#1e3a5f';
    $fontFamily = $style['fontFamily'] ?? 'Helvetica, Arial, sans-serif';

    $photoPath = null;
    if (!empty($profile['photoUrl'])) {
        $relative = preg_replace('#^/?storage/#', '', parse_url($profile['photoUrl'], PHP_URL_PATH) ?? '');
        $full = storage_path('app/public/' . $relative);
        if (is_file($full)) { $photoPath = $full; }
    }
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $doc->title }}</title>
    <style>
        @page { margin: 0; }
        body { font-family: {{ $fontFamily }}; color: #1f2937; font-size: 11px; margin: 0; }
        .band { background-color: {{ $primaryColor }}; color: #fff; padding: 16mm 18mm; overflow: hidden; }
        .band .photo { float: right; width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid rgba(255,255,255,0.4); }
        .band h1 { font-size: 20px; text-transform: uppercase; margin: 0 0 4px; }
        .band p { font-size: 11px; opacity: 0.9; margin: 0; }
        .content { padding: 12mm 18mm; }
        h2.section { font-size: 11px; text-transform: uppercase; font-weight: bold; border-left: 3px solid {{ $primaryColor }}; padding-left: 8px; margin: 0 0 8px; }
        .summary { font-size: 10px; color: #4b5563; margin-bottom: 18px; }
        .edu-grid { overflow: hidden; margin-bottom: 18px; }
        .edu-item { float: left; width: 48%; margin-right: 2%; font-size: 9px; margin-bottom: 6px; }
        table.two-col { width: 100%; margin-bottom: 10px; }
        table.two-col td { vertical-align: top; width: 50%; padding-right: 12px; }
        .exp-item { font-size: 9px; margin-bottom: 8px; page-break-inside: avoid; }
        .exp-item .t { font-weight: bold; font-size: 10px; }
        .exp-item .c { color: #6b7280; }
        .footer { background-color: {{ $primaryColor }}; color: #fff; text-align: center; padding: 8mm; font-size: 9px; }
        .footer span { margin: 0 12px; }
    </style>
</head>
<body>
    <div class="band">
        @if($photoPath)<img src="{{ $photoPath }}" class="photo" alt="Photo">@endif
        <h1>{{ $profile['fullName'] ?? 'PRÉNOM NOM' }}</h1>
        <p>{{ $profile['title'] ?? 'Titre du poste' }}</p>
    </div>

    <div class="content">
        @if(!empty($profile['summary']))
            <h2 class="section">Profil</h2>
            <p class="summary">{{ $profile['summary'] }}</p>
        @endif

        @if(!empty($education))
            <h2 class="section">Éducation</h2>
            <div class="edu-grid">
                @foreach($education as $edu)
                    <div class="edu-item"><strong>{{ $edu['startDate'] ?? '' }} - {{ $edu['endDate'] ?? '' }} : {{ $edu['degree'] ?? '' }}</strong><br><span style="color:#6b7280;">{{ $edu['school'] ?? '' }}</span></div>
                @endforeach
            </div>
        @endif

        <table class="two-col">
            <tr>
                <td>
                    @if(!empty($experiences))
                        <h2 class="section">Expériences</h2>
                        @foreach($experiences as $exp)
                            <div class="exp-item">
                                <p class="t">{{ $exp['startDate'] ?? '' }} - {{ $exp['endDate'] ?? 'Présent' }} : {{ $exp['position'] ?? '' }}</p>
                                <p class="c">{{ $exp['company'] ?? '' }}</p>
                            </div>
                        @endforeach
                    @endif
                </td>
                <td>
                    @if(!empty($skills))
                        <h2 class="section">Compétences</h2>
                        <ul style="padding-left:14px; font-size:9px; color:#4b5563;">
                            @foreach($skills as $skill)<li>{{ $skill }}</li>@endforeach
                        </ul>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        @if(!empty($profile['email']))<span>{{ $profile['email'] }}</span>@endif
        @if(!empty($profile['phone']))<span>{{ $profile['phone'] }}</span>@endif
    </div>
</body>
</html>

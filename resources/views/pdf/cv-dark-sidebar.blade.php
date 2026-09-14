@php
    $content = $doc->content ?? [];
    $style = $doc->style ?? [];
    $profile = $content['profile'] ?? [];
    $experiences = $content['experiences'] ?? [];
    $education = $content['education'] ?? [];
    $skills = $profile['skills'] ?? [];
    $primaryColor = $style['primaryColor'] ?? '#eab308';
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
        body { font-family: {{ $fontFamily }}; font-size: 11px; margin: 0; }
        table.layout { width: 100%; border-collapse: collapse; height: 297mm; }
        td.sidebar { width: 36%; background-color: #1c1c1c; color: #fff; padding: 14mm; vertical-align: top; }
        td.main { width: 64%; padding: 16mm; vertical-align: top; }
        .photo { width: 100%; max-width: 120px; border-radius: 6px; object-fit: cover; margin-bottom: 16px; }
        .tag { display: inline-block; background-color: {{ $primaryColor }}; color: #111; font-size: 9px; font-weight: bold; text-transform: uppercase; padding: 3px 10px; border-radius: 3px; margin-bottom: 8px; }
        .edu-item { margin-bottom: 10px; font-size: 9px; }
        .edu-item .d { font-weight: bold; color: #fff; }
        .edu-item .s { color: #9ca3af; }
        .contact-item { font-size: 9px; background-color: rgba(255,255,255,0.08); padding: 5px 8px; border-radius: 3px; margin-bottom: 5px; }
        h1.name { font-size: 22px; font-weight: bold; text-transform: uppercase; color: #111; margin: 4px 0 16px; }
        .summary { font-size: 10px; color: #4b5563; margin-bottom: 16px; }
        .exp { overflow: hidden; margin-bottom: 10px; font-size: 10px; page-break-inside: avoid; }
        .exp .dates { float: left; width: 20%; color: #9ca3af; }
        .exp .body { float: left; width: 78%; }
        .exp .position { font-weight: bold; }
        .exp .company { color: #6b7280; font-size: 9px; }
        .skills-grid td { font-size: 9px; color: #4b5563; padding: 2px 0; width: 50%; }
    </style>
</head>
<body>
    <table class="layout">
        <tr>
            <td class="sidebar">
                @if($photoPath)<img src="{{ $photoPath }}" class="photo" alt="Photo">@endif
                @if(!empty($education))
                    <span class="tag">Education</span><br>
                    @foreach($education as $edu)
                        <div class="edu-item">
                            <div class="d">{{ $edu['degree'] ?? '' }}</div>
                            <div class="s">{{ $edu['school'] ?? '' }}</div>
                            <div class="s">{{ $edu['startDate'] ?? '' }} - {{ $edu['endDate'] ?? '' }}</div>
                        </div>
                    @endforeach
                @endif
                <div style="margin-top:20px; border-top:1px solid rgba(255,255,255,0.15); padding-top:12px;">
                    @if(!empty($profile['phone']))<div class="contact-item">{{ $profile['phone'] }}</div>@endif
                    @if(!empty($profile['email']))<div class="contact-item">{{ $profile['email'] }}</div>@endif
                    @if(!empty($profile['location']))<div class="contact-item">{{ $profile['location'] }}</div>@endif
                </div>
            </td>
            <td class="main">
                <span class="tag">{{ $profile['title'] ?? 'Titre du poste' }}</span>
                <h1 class="name">{{ $profile['fullName'] ?? 'Prénom Nom' }}</h1>

                @if(!empty($profile['summary']))
                    <span class="tag">About Me</span>
                    <p class="summary">{{ $profile['summary'] }}</p>
                @endif

                @if(!empty($experiences))
                    <span class="tag">Work Experience</span><br><br>
                    @foreach($experiences as $exp)
                        <div class="exp">
                            <div class="dates">{{ $exp['startDate'] ?? '' }}<br>{{ $exp['endDate'] ?? 'Présent' }}</div>
                            <div class="body">
                                <div class="position">{{ $exp['position'] ?? '' }}</div>
                                <div class="company">{{ $exp['company'] ?? '' }}</div>
                                <div style="color:#6b7280; font-size:9px; margin-top:2px;">{{ $exp['description'] ?? '' }}</div>
                            </div>
                        </div>
                    @endforeach
                @endif

                @if(!empty($skills))
                    <span class="tag">Software Skill</span>
                    <table class="skills-grid" style="width:100%; margin-top:6px;">
                        @foreach(array_chunk($skills, 2) as $pair)
                            <tr>
                                <td>{{ $pair[0] ?? '' }}</td>
                                <td>{{ $pair[1] ?? '' }}</td>
                            </tr>
                        @endforeach
                    </table>
                @endif
            </td>
        </tr>
    </table>
</body>
</html>

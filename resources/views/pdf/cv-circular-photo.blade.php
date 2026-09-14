@php
    $content = $doc->content ?? [];
    $style = $doc->style ?? [];
    $profile = $content['profile'] ?? [];
    $experiences = $content['experiences'] ?? [];
    $education = $content['education'] ?? [];
    $skills = $profile['skills'] ?? [];
    $primaryColor = $style['primaryColor'] ?? '#4f46e5';
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
        @page { margin: 18mm; }
        body { font-family: {{ $fontFamily }}; color: #1f2937; font-size: 11px; margin: 0; }
        .header { overflow: hidden; margin-bottom: 20px; }
        .photo { float: left; width: 70px; height: 70px; border-radius: 50%; object-fit: cover; margin-right: 16px; border: 3px solid #f3f4f6; }
        .header-text { overflow: hidden; padding-top: 6px; }
        .label { font-size: 9px; color: #9ca3af; margin: 0; }
        .name { font-size: 18px; font-weight: bold; margin: 2px 0; }
        .summary { font-size: 10px; color: #6b7280; margin-bottom: 20px; }
        h2.section { font-size: 11px; text-transform: uppercase; font-weight: bold; margin: 0 0 10px; }
        .edu-timeline { overflow: hidden; margin-bottom: 20px; }
        .edu-item { float: left; width: 32%; margin-right: 2%; border-top: 2px solid {{ $primaryColor }}; padding-top: 6px; font-size: 9px; }
        table.two-col { width: 100%; }
        table.two-col td { vertical-align: top; width: 50%; padding-right: 16px; }
        .contact-item { font-size: 9px; color: #4b5563; margin-bottom: 4px; }
        .contact-item .dot { display:inline-block; width:6px; height:6px; border-radius:50%; background-color: {{ $primaryColor }}; margin-right:6px; }
        .exp { padding-left: 10px; border-left: 2px solid #f3f4f6; margin-bottom: 10px; font-size: 9px; page-break-inside: avoid; }
        .exp .p { font-weight: bold; font-size: 10px; }
        .exp .c { color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        @if($photoPath)<img src="{{ $photoPath }}" class="photo" alt="Photo">@endif
        <div class="header-text">
            <p class="label">Position Title</p>
            <p style="font-size:9px;color:#6b7280;margin:0;">Hello I'm</p>
            <p class="name">{{ $profile['fullName'] ?? 'Prénom Nom' }}</p>
        </div>
    </div>

    @if(!empty($profile['summary']))<p class="summary">{{ $profile['summary'] }}</p>@endif

    @if(!empty($education))
        <h2 class="section">Education</h2>
        <div class="edu-timeline">
            @foreach($education as $edu)
                <div class="edu-item">
                    <strong>{{ $edu['degree'] ?? '' }}</strong><br>
                    <span style="color:#6b7280;">{{ $edu['school'] ?? '' }}</span><br>
                    <span style="color:#9ca3af;">{{ $edu['startDate'] ?? '' }} - {{ $edu['endDate'] ?? '' }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <table class="two-col">
        <tr>
            <td>
                <h2 class="section">Contact</h2>
                @if(!empty($profile['location']))<p class="contact-item"><span class="dot"></span>{{ $profile['location'] }}</p>@endif
                @if(!empty($profile['phone']))<p class="contact-item"><span class="dot"></span>{{ $profile['phone'] }}</p>@endif
                @if(!empty($profile['email']))<p class="contact-item"><span class="dot"></span>{{ $profile['email'] }}</p>@endif

                @if(!empty($skills))
                    <h2 class="section" style="margin-top:16px;">Expertise</h2>
                    <ul style="padding-left:12px; font-size:9px; color:#4b5563;">
                        @foreach($skills as $skill)<li>{{ $skill }}</li>@endforeach
                    </ul>
                @endif
            </td>
            <td>
                @if(!empty($experiences))
                    <h2 class="section">Work Experience</h2>
                    @foreach($experiences as $exp)
                        <div class="exp">
                            <div class="p">{{ $exp['position'] ?? '' }}</div>
                            <div class="c">{{ $exp['company'] ?? '' }} | {{ $exp['startDate'] ?? '' }} - {{ $exp['endDate'] ?? 'Présent' }}</div>
                            <div style="color:#6b7280; margin-top:2px;">{{ $exp['description'] ?? '' }}</div>
                        </div>
                    @endforeach
                @endif
            </td>
        </tr>
    </table>
</body>
</html>

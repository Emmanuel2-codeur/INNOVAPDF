@php
    $content = $doc->content ?? [];
    $style = $doc->style ?? [];
    $profile = $content['profile'] ?? [];
    $experiences = $content['experiences'] ?? [];
    $education = $content['education'] ?? [];
    $skills = $profile['skills'] ?? [];
    $primaryColor = $style['primaryColor'] ?? '#111827';
    $fontFamily = $style['fontFamily'] ?? 'Helvetica, Arial, sans-serif';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $doc->title }}</title>
    <style>
        @page { margin: 18mm; }
        body { font-family: {{ $fontFamily }}; color: #1f2937; font-size: 11px; margin: 0; border-left: 4px solid {{ $primaryColor }}; padding-left: 20px; }
        .header { overflow: hidden; margin-bottom: 20px; }
        .header .left { float: left; width: 60%; }
        .header .right { float: right; text-align: right; font-size: 9px; color: #6b7280; }
        .label { text-transform: uppercase; letter-spacing: 2px; font-size: 8px; color: #9ca3af; margin: 0; }
        .name { font-size: 24px; font-weight: bold; margin: 4px 0; }
        .summary { font-size: 10px; color: #6b7280; margin-bottom: 20px; max-width: 380px; }
        h2.section { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; font-weight: bold; margin: 0 0 10px; }
        .edu-row { overflow: hidden; margin-bottom: 20px; }
        .edu-col { float: left; width: 32%; margin-right: 2%; border-top: 2px solid {{ $primaryColor }}; padding-top: 6px; font-size: 9px; }
        table.layout { width: 100%; }
        table.layout td { vertical-align: top; }
        .col-left { width: 30%; padding-right: 20px; font-size: 9px; color: #4b5563; }
        .col-left li { margin-bottom: 4px; }
        .exp { margin-bottom: 12px; padding-left: 10px; border-left: 1px solid #e5e7eb; page-break-inside: avoid; }
        .exp .position { font-weight: bold; font-size: 11px; }
        .exp .meta { font-size: 9px; color: #6b7280; }
        .exp .desc { font-size: 9px; color: #6b7280; margin-top: 3px; white-space: pre-line; }
    </style>
</head>
<body>
    <div class="header">
        <div class="left">
            <p class="label">Position Title</p>
            <p style="font-size:10px;color:#6b7280;margin:0;">Hello I'm</p>
            <p class="name">{{ $profile['fullName'] ?? 'Prénom Nom' }}</p>
        </div>
        <div class="right">
            @if(!empty($profile['phone']))<p>{{ $profile['phone'] }}</p>@endif
            @if(!empty($profile['email']))<p>{{ $profile['email'] }}</p>@endif
            @if(!empty($profile['location']))<p>{{ $profile['location'] }}</p>@endif
        </div>
    </div>

    @if(!empty($profile['summary']))<p class="summary">{{ $profile['summary'] }}</p>@endif

    @if(!empty($education))
        <h2 class="section">Education</h2>
        <div class="edu-row">
            @foreach($education as $edu)
                <div class="edu-col">
                    <strong>{{ $edu['degree'] ?? '' }}</strong><br>
                    {{ $edu['school'] ?? '' }}<br>
                    <span style="color:#9ca3af;">{{ $edu['startDate'] ?? '' }} - {{ $edu['endDate'] ?? '' }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <table class="layout">
        <tr>
            <td class="col-left">
                @if(!empty($skills))
                    <h2 class="section">Expertise</h2>
                    <ul style="padding-left:12px; margin:0;">
                        @foreach($skills as $skill)<li>{{ $skill }}</li>@endforeach
                    </ul>
                @endif
            </td>
            <td>
                @if(!empty($experiences))
                    <h2 class="section">Work Experience</h2>
                    @foreach($experiences as $exp)
                        <div class="exp">
                            <span class="position">{{ $exp['position'] ?? '' }}</span>
                            <div class="meta">{{ $exp['company'] ?? '' }} | {{ $exp['startDate'] ?? '' }} - {{ $exp['endDate'] ?? 'Présent' }}</div>
                            <div class="desc">{{ $exp['description'] ?? '' }}</div>
                        </div>
                    @endforeach
                @endif
            </td>
        </tr>
    </table>
</body>
</html>

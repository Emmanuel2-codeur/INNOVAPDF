@php
    $content = $doc->content ?? [];
    $style = $doc->style ?? [];
    $profile = $content['profile'] ?? [];
    $experiences = $content['experiences'] ?? [];
    $primaryColor = $style['primaryColor'] ?? '#4f46e5';
    $fontFamily = $style['fontFamily'] ?? 'Helvetica, Arial, sans-serif';
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $doc->title }}</title>
    <style>
        @page { margin: 22mm 18mm; }
        body { font-family: {{ $fontFamily }}; color: #1f2937; font-size: 11px; }
        h1 { font-size: 24px; text-transform: uppercase; letter-spacing: 1px; color: {{ $primaryColor }}; margin: 0; }
        .subtitle { font-size: 14px; color: #4b5563; margin: 4px 0 10px; }
        .contact { font-size: 9px; color: #6b7280; }
        .contact span { margin-right: 14px; }
        header { border-bottom: 2px solid {{ $primaryColor }}; padding-bottom: 14px; margin-bottom: 20px; }
        h2.section { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: {{ $primaryColor }}; margin: 0 0 10px; }
        .experience { border-left: 2px solid {{ $primaryColor }}; padding-left: 10px; margin-bottom: 14px; page-break-inside: avoid; }
        .experience .row { width: 100%; }
        .experience .position { font-weight: bold; font-size: 12px; color: #111827; }
        .experience .dates { float: right; font-size: 9px; color: #6b7280; }
        .experience .company { font-size: 10px; font-weight: bold; color: #374151; margin: 2px 0; }
        .experience .description { font-size: 10px; color: #4b5563; white-space: pre-line; }
    </style>
</head>
<body>
    <header>
        <h1>{{ $profile['fullName'] ?? 'Nom Prénom' }}</h1>
        <p class="subtitle">{{ $profile['title'] ?? 'Titre du poste' }}</p>
        <p class="contact">
            @if(!empty($profile['email']))<span>{{ $profile['email'] }}</span>@endif
            @if(!empty($profile['phone']))<span>{{ $profile['phone'] }}</span>@endif
            @if(!empty($profile['location']))<span>{{ $profile['location'] }}</span>@endif
        </p>
    </header>

    @if(!empty($experiences))
        <section>
            <h2 class="section">Expériences Professionnelles</h2>
            @foreach($experiences as $exp)
                <div class="experience">
                    <div class="row">
                        <span class="position">{{ $exp['position'] ?? '' }}</span>
                        <span class="dates">{{ $exp['startDate'] ?? '' }} - {{ $exp['endDate'] ?? 'Présent' }}</span>
                    </div>
                    <div class="company">{{ $exp['company'] ?? '' }}</div>
                    <div class="description">{{ $exp['description'] ?? '' }}</div>
                </div>
            @endforeach
        </section>
    @endif
</body>
</html>
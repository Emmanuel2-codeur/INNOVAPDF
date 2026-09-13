@php
    $content = $doc->content ?? [];
    $style = $doc->style ?? [];
    $profile = $content['profile'] ?? [];
    $experiences = $content['experiences'] ?? [];
    $primaryColor = $style['primaryColor'] ?? '#4f46e5';
    $fontFamily = $style['fontFamily'] ?? 'Helvetica, Arial, sans-serif';

    // Dompdf a besoin d'un chemin de fichier local (pas d'une URL http) pour
    // afficher une image sans activer les requêtes réseau distantes.
    $photoPath = null;
    if (!empty($profile['photoUrl'])) {
        $relative = preg_replace('#^/?storage/#', '', parse_url($profile['photoUrl'], PHP_URL_PATH) ?? '');
        $full = storage_path('app/public/' . $relative);
        if (is_file($full)) {
            $photoPath = $full;
        }
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
        table.layout { width: 100%; border-collapse: collapse; height: 297mm; }
        td.sidebar { width: 33%; background-color: {{ $primaryColor }}; color: #fff; padding: 24mm 10mm; vertical-align: top; }
        td.main { width: 67%; padding: 24mm 14mm; vertical-align: top; }
        .photo { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; display: block; margin: 0 auto 14px; border: 2px solid rgba(255,255,255,0.5); }
        .name { font-size: 17px; font-weight: bold; text-transform: uppercase; text-align: center; letter-spacing: 1px; margin: 0; }
        .job-title { font-size: 10px; text-align: center; opacity: 0.9; margin: 4px 0 20px; }
        .side-section { margin-top: 18px; border-top: 1px solid rgba(255,255,255,0.25); padding-top: 12px; }
        .side-section h3 { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 8px; }
        .side-section p { font-size: 9px; margin: 0 0 6px; opacity: 0.95; word-wrap: break-word; }
        h2.section { font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: {{ $primaryColor }}; border-bottom: 2px solid {{ $primaryColor }}; padding-bottom: 4px; margin: 0 0 14px; }
        .experience { margin-bottom: 14px; page-break-inside: avoid; }
        .experience .position { font-weight: bold; font-size: 12px; color: #111827; }
        .experience .dates { float: right; font-size: 9px; color: #6b7280; }
        .experience .company { font-size: 10px; font-weight: bold; color: #4b5563; margin: 2px 0; }
        .experience .description { font-size: 10px; color: #4b5563; white-space: pre-line; }
    </style>
</head>
<body>
    <table class="layout">
        <tr>
            <td class="sidebar">
                @if($photoPath)<img src="{{ $photoPath }}" class="photo" alt="Photo">@endif
                <p class="name">{{ $profile['fullName'] ?? 'Nom Prénom' }}</p>
                <p class="job-title">{{ $profile['title'] ?? 'Titre du poste' }}</p>

                <div class="side-section">
                    <h3>Contact</h3>
                    @if(!empty($profile['email']))<p>{{ $profile['email'] }}</p>@endif
                    @if(!empty($profile['phone']))<p>{{ $profile['phone'] }}</p>@endif
                    @if(!empty($profile['location']))<p>{{ $profile['location'] }}</p>@endif
                </div>

                @if(!empty($profile['summary']))
                    <div class="side-section">
                        <h3>À propos</h3>
                        <p>{{ $profile['summary'] }}</p>
                    </div>
                @endif
            </td>
            <td class="main">
                @if(!empty($experiences))
                    <h2 class="section">Expériences Professionnelles</h2>
                    @foreach($experiences as $exp)
                        <div class="experience">
                            <span class="position">{{ $exp['position'] ?? '' }}</span>
                            <span class="dates">{{ $exp['startDate'] ?? '' }} - {{ $exp['endDate'] ?? 'Présent' }}</span>
                            <div class="company">{{ $exp['company'] ?? '' }}</div>
                            <div class="description">{{ $exp['description'] ?? '' }}</div>
                        </div>
                    @endforeach
                @endif
            </td>
        </tr>
    </table>
</body>
</html>

@php
    $content = $doc->content ?? [];
    $style = $doc->style ?? [];
    $profile = $content['profile'] ?? [];
    $items = $content['invoiceItems'] ?? [];
    $primaryColor = $style['primaryColor'] ?? '#4f46e5';
    $fontFamily = $style['fontFamily'] ?? 'Helvetica, Arial, sans-serif';

    $invoiceMeta = $content['invoiceMeta'] ?? [];
    $taxRate = (float) ($invoiceMeta['taxRate'] ?? 0.18);
    $currency = $invoiceMeta['currency'] ?? 'FCFA';
    $subtotal = array_reduce($items, fn ($sum, $item) => $sum + (($item['quantity'] ?? 0) * ($item['unitPrice'] ?? 0)), 0);
    $taxAmount = $subtotal * $taxRate;
    $total = $subtotal + $taxAmount;

    $money = fn ($n) => number_format((float) $n, 0, ',', ' ') . ' ' . $currency;
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $doc->title }}</title>
    <style>
        @page { margin: 20mm 18mm; }
        body { font-family: {{ $fontFamily }}; color: #1f2937; font-size: 11px; }
        .header-table { width: 100%; border-bottom: 1px solid #e5e7eb; padding-bottom: 18px; margin-bottom: 26px; }
        .header-table td { vertical-align: top; }
        h1 { font-size: 24px; font-weight: bold; letter-spacing: 1px; color: {{ $primaryColor }}; margin: 0; }
        .ref { font-size: 9px; color: #6b7280; margin-top: 4px; }
        .issuer { text-align: right; font-size: 9px; color: #4b5563; }
        .issuer .name { font-weight: bold; color: #111827; font-size: 11px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        table.items th { text-align: left; font-size: 9px; text-transform: uppercase; border-bottom: 2px solid {{ $primaryColor }}; padding: 6px 4px; color: #374151; }
        table.items td { font-size: 10px; padding: 8px 4px; border-bottom: 1px solid #f3f4f6; color: #374151; }
        table.items th.num, table.items td.num { text-align: right; }
        table.items th.center, table.items td.center { text-align: center; }
        table.totals { width: 45%; margin-left: 55%; font-size: 10px; }
        table.totals td { padding: 4px 0; }
        table.totals td.label { color: #6b7280; }
        table.totals td.value { text-align: right; font-weight: bold; color: #111827; }
        table.totals tr.total td { border-top: 1px solid #d1d5db; padding-top: 8px; font-size: 13px; color: {{ $primaryColor }}; }
        .footer { margin-top: 40px; text-align: center; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 12px; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 50%;">
                <h1>FACTURE</h1>
                <p class="ref">Réf : {{ $doc->title ?: 'FACT-2026-001' }}</p>
            </td>
            <td class="issuer" style="width: 50%;">
                <p class="name">{{ $profile['fullName'] ?? 'Votre Nom / Entreprise' }}</p>
                @if(!empty($profile['email']))<p>{{ $profile['email'] }}</p>@endif
                @if(!empty($profile['phone']))<p>{{ $profile['phone'] }}</p>@endif
                @if(!empty($profile['location']))<p>{{ $profile['location'] }}</p>@endif
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Désignation</th>
                <th class="center">Quantité</th>
                <th class="num">Prix Unitaire</th>
                <th class="num">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item['description'] ?? "Description de l'article" }}</td>
                    <td class="center">{{ $item['quantity'] ?? 0 }}</td>
                    <td class="num">{{ $money($item['unitPrice'] ?? 0) }}</td>
                    <td class="num">{{ $money(($item['quantity'] ?? 0) * ($item['unitPrice'] ?? 0)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="label">Sous-total HT :</td>
            <td class="value">{{ $money($subtotal) }}</td>
        </tr>
        <tr>
            <td class="label">TVA ({{ rtrim(rtrim(number_format($taxRate * 100, 1), '0'), '.') }}%) :</td>
            <td class="value">{{ $money($taxAmount) }}</td>
        </tr>
        <tr class="total">
            <td class="label">Total TTC :</td>
            <td class="value">{{ $money($total) }}</td>
        </tr>
    </table>

    <div class="footer">Merci pour votre confiance. Facture générée via INNOVAPDF.</div>
</body>
</html>
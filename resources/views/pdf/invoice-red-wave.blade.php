@php
    $content = $doc->content ?? [];
    $style = $doc->style ?? [];
    $profile = $content['profile'] ?? [];
    $items = $content['invoiceItems'] ?? [];
    $primaryColor = $style['primaryColor'] ?? '#dc2626';
    $fontFamily = $style['fontFamily'] ?? 'Helvetica, Arial, sans-serif';
    $label = $doc->type === 'quote' ? 'Devis' : 'Invoice/Bile';

    $invoiceMeta = $content['invoiceMeta'] ?? [];
    $taxRate = (float) ($invoiceMeta['taxRate'] ?? 0.18);
    $currency = $invoiceMeta['currency'] ?? 'FCFA';
    $subtotal = array_reduce($items, fn ($sum, $item) => $sum + (($item['quantity'] ?? 0) * ($item['unitPrice'] ?? 0)), 0);
    $total = $subtotal * (1 + $taxRate);
    $money = fn ($n) => number_format((float) $n, 0, ',', ' ') . ' ' . $currency;
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $doc->title }}</title>
    <style>
        @page { margin: 20mm 20mm 0; }
        body { font-family: {{ $fontFamily }}; color: #1f2937; font-size: 11px; }
        .header { overflow: hidden; margin-bottom: 24px; }
        .header .logo { float: left; width: 26px; height: 26px; background-color: {{ $primaryColor }}; border-radius: 5px; color: #fff; text-align: center; line-height: 26px; font-size: 9px; font-weight: bold; margin-right: 8px; }
        .header .brand { font-size: 11px; font-weight: bold; }
        .header .tagline { font-size: 8px; color: #9ca3af; }
        .header .label { float: right; font-size: 18px; font-weight: bold; }
        .ref { font-size: 9px; color: #6b7280; overflow: hidden; margin-bottom: 20px; }
        .ref .r { float: right; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { text-align: left; background-color: {{ $primaryColor }}; color: #fff; padding: 6px 8px; font-size: 9px; }
        table.items td { padding: 6px 8px; font-size: 10px; border-bottom: 1px solid #f3f4f6; }
        table.items th.r, table.items td.r { text-align: right; }
        table.items th.c, table.items td.c { text-align: center; }
        .total-line { text-align: right; font-weight: bold; font-size: 12px; margin-bottom: 30px; }
        .footer-wave { background-color: {{ $primaryColor }}; height: 12mm; border-radius: 50% 50% 0 0 / 100% 100% 0 0; margin: 0 -20mm; }
        .footer-band { background-color: #1c1c1c; color: #fff; text-align: center; font-size: 9px; padding: 6mm 20mm; margin: 0 -20mm; }
    </style>
</head>
<body>
    <div class="header">
        <span class="logo">IP</span>
        <p class="brand">{{ $profile['fullName'] ?? 'BRANDTEXT' }}</p>
        <p class="tagline">TAGLINE SPACE</p>
        <span class="label">{{ $label }}</span>
    </div>

    <div class="ref">
        Réf : {{ $doc->title ?: 'DOC-001' }}
        <span class="r">Date : {{ now()->format('d/m/Y') }}</span>
    </div>

    <table class="items">
        <thead><tr><th>N°</th><th>Description</th><th class="c">Qté</th><th class="r">P.U.</th><th class="r">Total</th></tr></thead>
        <tbody>
            @foreach($items as $i => $item)
                <tr>
                    <td>{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $item['description'] ?? "Description de l'article" }}</td>
                    <td class="c">{{ $item['quantity'] ?? 0 }}</td>
                    <td class="r">{{ $money($item['unitPrice'] ?? 0) }}</td>
                    <td class="r">{{ $money(($item['quantity'] ?? 0) * ($item['unitPrice'] ?? 0)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="total-line">Total : {{ $money($total) }}</p>

    <div class="footer-wave"></div>
    <div class="footer-band">
        @if(!empty($profile['phone']))<span style="margin:0 12px;">{{ $profile['phone'] }}</span>@endif
        @if(!empty($profile['email']))<span style="margin:0 12px;">{{ $profile['email'] }}</span>@endif
    </div>
</body>
</html>

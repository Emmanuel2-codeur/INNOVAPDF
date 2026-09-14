@php
    $content = $doc->content ?? [];
    $style = $doc->style ?? [];
    $profile = $content['profile'] ?? [];
    $items = $content['invoiceItems'] ?? [];
    $primaryColor = $style['primaryColor'] ?? '#f59e0b';
    $fontFamily = $style['fontFamily'] ?? 'Helvetica, Arial, sans-serif';
    $label = $doc->type === 'quote' ? 'DEVIS' : 'INVOICE';

    $invoiceMeta = $content['invoiceMeta'] ?? [];
    $taxRate = (float) ($invoiceMeta['taxRate'] ?? 0.07);
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
        @page { margin: 0; }
        body { font-family: {{ $fontFamily }}; color: #1f2937; font-size: 11px; margin: 0; }
        .band { background-color: #1c1c1c; color: #fff; padding: 16mm 18mm 20mm; }
        .band .name { font-weight: bold; font-size: 16px; }
        .band .tagline { font-size: 9px; opacity: 0.7; }
        .band .label { float: right; font-size: 26px; font-weight: 900; color: {{ $primaryColor }}; }
        .content { padding: 0 18mm 16mm; margin-top: -12mm; }
        .meta-box { background-color: #fff; box-shadow: 0 1px 4px rgba(0,0,0,0.1); padding: 12px; margin-bottom: 20px; font-size: 9px; overflow: hidden; }
        .meta-box .left { float: left; width: 50%; }
        .meta-box .right { float: right; width: 50%; text-align: right; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { text-align: left; background-color: #1c1c1c; color: #fff; padding: 8px; font-size: 9px; }
        table.items td { padding: 8px; font-size: 10px; border-bottom: 1px solid #f3f4f6; }
        table.items th.r, table.items td.r { text-align: right; }
        table.items th.c, table.items td.c { text-align: center; }
        table.totals { width: 45%; margin-left: 55%; font-size: 10px; }
        table.totals .total td { font-weight: bold; font-size: 13px; color: {{ $primaryColor }}; padding-top: 6px; }
    </style>
</head>
<body>
    <div class="band">
        <span class="label">{{ $label }}</span>
        <p class="name">{{ $profile['fullName'] ?? 'Company Name' }}</p>
        <p class="tagline">Votre slogan ici</p>
    </div>

    <div class="content">
        <div class="meta-box">
            <div class="left">
                <strong>Facturé à</strong><br>
                {{ $profile['email'] ?? '' }}<br>{{ $profile['phone'] ?? '' }}
            </div>
            <div class="right">
                Réf : {{ $doc->title ?: 'DOC-001' }}<br>
                Date : {{ now()->format('d/m/Y') }}
            </div>
        </div>

        <table class="items">
            <thead><tr><th>Description</th><th class="r">Prix</th><th class="c">Qté</th><th class="r">Total</th></tr></thead>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item['description'] ?? "Description de l'article" }}</td>
                        <td class="r">{{ $money($item['unitPrice'] ?? 0) }}</td>
                        <td class="c">{{ $item['quantity'] ?? 0 }}</td>
                        <td class="r">{{ $money(($item['quantity'] ?? 0) * ($item['unitPrice'] ?? 0)) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tr><td>Sous-total :</td><td class="r">{{ $money($subtotal) }}</td></tr>
            <tr><td>TVA {{ rtrim(rtrim(number_format($taxRate * 100, 1), '0'), '.') }}% :</td><td class="r">{{ $money($taxAmount) }}</td></tr>
            <tr class="total"><td>Total :</td><td class="r">{{ $money($total) }}</td></tr>
        </table>
    </div>
</body>
</html>

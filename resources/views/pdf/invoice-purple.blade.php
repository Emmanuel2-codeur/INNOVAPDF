@php
    $content = $doc->content ?? [];
    $style = $doc->style ?? [];
    $profile = $content['profile'] ?? [];
    $items = $content['invoiceItems'] ?? [];
    $primaryColor = $style['primaryColor'] ?? '#6d28d9';
    $fontFamily = $style['fontFamily'] ?? 'Helvetica, Arial, sans-serif';
    $label = $doc->type === 'quote' ? 'DEVIS' : 'INVOICE';

    $invoiceMeta = $content['invoiceMeta'] ?? [];
    $taxRate = (float) ($invoiceMeta['taxRate'] ?? 0.15);
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
        @page { margin: 20mm; }
        body { font-family: {{ $fontFamily }}; color: #1f2937; font-size: 11px; }
        .header { overflow: hidden; margin-bottom: 30px; }
        .header .circle { width: 22px; height: 22px; border: 4px solid {{ $primaryColor }}; border-radius: 50%; margin-bottom: 6px; }
        .header .brand { font-weight: bold; color: {{ $primaryColor }}; }
        .header .label { float: right; font-size: 22px; font-weight: bold; color: {{ $primaryColor }}; letter-spacing: 1px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.items th { text-align: left; background-color: {{ $primaryColor }}; color: #fff; padding: 8px 10px; font-size: 10px; }
        table.items th.r, table.items td.r { text-align: right; }
        table.items td { padding: 8px 10px; font-size: 10px; border-bottom: 1px solid #f3f4f6; }
        table.totals { width: 45%; margin-left: 55%; font-size: 10px; }
        table.totals td { padding: 3px 0; }
        table.totals .lbl { text-transform: uppercase; font-weight: bold; color: {{ $primaryColor }}; }
        table.totals .total td { background-color: {{ $primaryColor }}; color: #fff; padding: 8px; font-weight: bold; font-size: 12px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="circle"></div>
        <p class="brand">{{ $profile['fullName'] ?? 'Business Name' }}</p>
        <span class="label">{{ $label }}</span>
    </div>

    <table class="items">
        <thead>
            <tr><th>Description</th><th class="r">P.U.</th><th class="r">Qté</th><th class="r">Total</th></tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item['description'] ?? "Description de l'article" }}</td>
                    <td class="r">{{ $money($item['unitPrice'] ?? 0) }}</td>
                    <td class="r">{{ $item['quantity'] ?? 0 }}</td>
                    <td class="r">{{ $money(($item['quantity'] ?? 0) * ($item['unitPrice'] ?? 0)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td class="lbl">Sous-total :</td><td class="r">{{ $money($subtotal) }}</td></tr>
        <tr><td class="lbl">TVA {{ rtrim(rtrim(number_format($taxRate * 100, 1), '0'), '.') }}% :</td><td class="r">{{ $money($taxAmount) }}</td></tr>
        <tr class="total"><td>Total dû :</td><td class="r">{{ $money($total) }}</td></tr>
    </table>
</body>
</html>

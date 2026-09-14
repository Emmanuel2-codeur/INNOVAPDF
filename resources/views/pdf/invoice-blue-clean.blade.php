@php
    $content = $doc->content ?? [];
    $style = $doc->style ?? [];
    $profile = $content['profile'] ?? [];
    $items = $content['invoiceItems'] ?? [];
    $primaryColor = $style['primaryColor'] ?? '#2563eb';
    $fontFamily = $style['fontFamily'] ?? 'Helvetica, Arial, sans-serif';
    $label = $doc->type === 'quote' ? 'DEVIS' : 'INVOICE';

    $invoiceMeta = $content['invoiceMeta'] ?? [];
    $taxRate = (float) ($invoiceMeta['taxRate'] ?? 0.10);
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
        .header { overflow: hidden; margin-bottom: 24px; }
        .header .brand { font-weight: bold; font-size: 12px; }
        .header .label { float: right; font-size: 22px; font-weight: bold; color: {{ $primaryColor }}; }
        .client { font-size: 10px; margin-bottom: 20px; }
        .client .name { font-weight: bold; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { text-align: left; border-bottom: 2px solid {{ $primaryColor }}; padding: 6px 0; font-size: 10px; }
        table.items td { padding: 6px 0; font-size: 10px; border-bottom: 1px solid #f3f4f6; }
        table.items th.r, table.items td.r { text-align: right; }
        table.totals { width: 45%; margin-left: 55%; font-size: 10px; }
        table.totals td { padding: 2px 0; color: #6b7280; }
        table.totals .total td { font-weight: bold; color: #fff; background-color: {{ $primaryColor }}; padding: 6px 8px; }
    </style>
</head>
<body>
    <div class="header">
        <p class="brand">{{ $profile['fullName'] ?? 'Company' }}</p>
        <span class="label">{{ $label }}</span>
    </div>

    <div class="client">
        <p class="name">{{ $profile['fullName'] ?? 'Client' }}</p>
        <p style="color:#6b7280;">{{ $profile['phone'] ?? '' }} · {{ $profile['email'] ?? '' }}</p>
    </div>

    <table class="items">
        <thead><tr><th>Description du produit</th><th class="r">Prix</th><th class="r">Qté</th><th class="r">Total</th></tr></thead>
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
        <tr><td>Subtotal :</td><td class="r">{{ $money($subtotal) }}</td></tr>
        <tr><td>Tax {{ rtrim(rtrim(number_format($taxRate * 100, 1), '0'), '.') }}% :</td><td class="r">{{ $money($taxAmount) }}</td></tr>
        <tr class="total"><td>Total :</td><td class="r">{{ $money($total) }}</td></tr>
    </table>
</body>
</html>

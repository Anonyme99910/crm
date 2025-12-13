<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; direction: rtl; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #1a56db; margin: 0; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; text-align: right; }
        .items-table th { background-color: #1a56db; color: white; }
        .totals { width: 300px; margin-right: auto; }
        .totals td { padding: 5px; }
        .footer { margin-top: 30px; text-align: center; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>عرض سعر</h1>
        <p>رقم: {{ $quotation->quotation_number }}</p>
    </div>
    
    <table class="info-table">
        <tr>
            <td><strong>العميل:</strong> {{ $quotation->lead->name }}</td>
            <td><strong>التاريخ:</strong> {{ $quotation->created_at->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td><strong>الهاتف:</strong> {{ $quotation->lead->phone }}</td>
            <td><strong>صالح حتى:</strong> {{ $quotation->valid_until->format('Y-m-d') }}</td>
        </tr>
    </table>

    <h3>{{ $quotation->title }}</h3>
    @if($quotation->description)
        <p>{{ $quotation->description }}</p>
    @endif

    <table class="items-table">
        <thead>
            <tr>
                <th>#</th>
                <th>البند</th>
                <th>الوحدة</th>
                <th>الكمية</th>
                <th>سعر الوحدة</th>
                <th>الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->unit }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->unit_price, 2) }}</td>
                <td>{{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>الإجمالي الفرعي:</td>
            <td>{{ number_format($quotation->subtotal, 2) }} ج.م</td>
        </tr>
        @if($quotation->discount_amount > 0)
        <tr>
            <td>الخصم:</td>
            <td>{{ number_format($quotation->discount_amount, 2) }} ج.م</td>
        </tr>
        @endif
        @if($quotation->tax_amount > 0)
        <tr>
            <td>الضريبة ({{ $quotation->tax_rate }}%):</td>
            <td>{{ number_format($quotation->tax_amount, 2) }} ج.م</td>
        </tr>
        @endif
        <tr style="font-weight: bold; font-size: 14px;">
            <td>الإجمالي:</td>
            <td>{{ number_format($quotation->total, 2) }} ج.م</td>
        </tr>
    </table>

    @if($quotation->terms_conditions)
    <h4>الشروط والأحكام:</h4>
    <p>{{ $quotation->terms_conditions }}</p>
    @endif

    <div class="footer">
        <p>شكراً لثقتكم بنا</p>
    </div>
</body>
</html>

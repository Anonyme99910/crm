<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; direction: rtl; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #1a56db; padding-bottom: 20px; }
        .header h1 { color: #1a56db; margin: 0; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; text-align: right; }
        .items-table th { background-color: #1a56db; color: white; }
        .totals { width: 300px; margin-right: auto; }
        .totals td { padding: 5px; }
        .footer { margin-top: 30px; text-align: center; color: #666; border-top: 1px solid #ddd; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>فاتورة</h1>
        <p>رقم: {{ $invoice->invoice_number }}</p>
    </div>
    
    <table class="info-table">
        <tr>
            <td><strong>العميل:</strong> {{ $invoice->project->lead->name }}</td>
            <td><strong>تاريخ الإصدار:</strong> {{ $invoice->issue_date->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td><strong>المشروع:</strong> {{ $invoice->project->name }}</td>
            <td><strong>تاريخ الاستحقاق:</strong> {{ $invoice->due_date->format('Y-m-d') }}</td>
        </tr>
    </table>

    <h3>{{ $invoice->title }}</h3>

    <table class="items-table">
        <thead>
            <tr>
                <th>#</th>
                <th>الوصف</th>
                <th>الكمية</th>
                <th>سعر الوحدة</th>
                <th>الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->description }}</td>
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
            <td>{{ number_format($invoice->subtotal, 2) }} ج.م</td>
        </tr>
        @if($invoice->tax_amount > 0)
        <tr>
            <td>الضريبة ({{ $invoice->tax_rate }}%):</td>
            <td>{{ number_format($invoice->tax_amount, 2) }} ج.م</td>
        </tr>
        @endif
        <tr style="font-weight: bold; font-size: 14px;">
            <td>الإجمالي:</td>
            <td>{{ number_format($invoice->total, 2) }} ج.م</td>
        </tr>
        <tr>
            <td>المدفوع:</td>
            <td>{{ number_format($invoice->paid_amount, 2) }} ج.م</td>
        </tr>
        <tr style="font-weight: bold; color: #dc2626;">
            <td>المتبقي:</td>
            <td>{{ number_format($invoice->total - $invoice->paid_amount, 2) }} ج.م</td>
        </tr>
    </table>

    <div class="footer">
        <p>شكراً لتعاملكم معنا</p>
    </div>
</body>
</html>

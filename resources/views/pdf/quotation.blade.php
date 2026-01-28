<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @font-face {
            font-family: 'DejaVu Sans';
            src: url('{{ storage_path("fonts/DejaVuSans.ttf") }}') format('truetype');
        }
        * { font-family: 'DejaVu Sans', sans-serif; }
        body { direction: rtl; font-size: 12px; line-height: 1.6; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #d4af37; }
        .header .company { font-size: 20px; color: #d4af37; font-weight: bold; margin-bottom: 5px; }
        .header h1 { color: #1f2937; margin: 10px 0; font-size: 24px; }
        .header .number { color: #6b7280; font-size: 14px; }
        .info-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px; margin-bottom: 20px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 8px 5px; vertical-align: top; }
        .info-table .label { color: #6b7280; font-size: 11px; }
        .info-table .value { font-weight: bold; }
        .section-title { font-size: 14px; font-weight: bold; color: #1f2937; margin: 20px 0 10px; padding-bottom: 5px; border-bottom: 2px solid #d4af37; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { background: linear-gradient(135deg, #1f2937 0%, #374151 100%); color: white; padding: 12px 8px; text-align: right; font-size: 11px; }
        .items-table td { border: 1px solid #e5e7eb; padding: 10px 8px; text-align: right; }
        .items-table tr:nth-child(even) { background-color: #f9fafb; }
        .totals { width: 280px; margin-right: auto; margin-top: 20px; }
        .totals td { padding: 8px 5px; }
        .totals .total-row { font-weight: bold; font-size: 16px; border-top: 2px solid #1f2937; }
        .totals .total-row td { padding-top: 12px; }
        .terms { background: #fffbeb; border: 1px solid #fcd34d; border-radius: 8px; padding: 15px; margin-top: 20px; }
        .terms h4 { color: #92400e; margin: 0 0 10px; }
        .terms p { margin: 0; color: #78350f; font-size: 11px; white-space: pre-line; }
        .footer { margin-top: 40px; text-align: center; color: #9ca3af; font-size: 10px; border-top: 1px solid #e5e7eb; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">حازم عبدالله للتشطيبات</div>
        <h1>عرض سعر</h1>
        <div class="number">رقم: {{ $quotation->quotation_number }}</div>
    </div>
    
    <div class="info-box">
        <table class="info-table">
            <tr>
                <td width="50%">
                    <div class="label">العميل</div>
                    <div class="value">{{ $quotation->lead->name ?? '-' }}</div>
                </td>
                <td width="50%">
                    <div class="label">تاريخ الإنشاء</div>
                    <div class="value">{{ $quotation->created_at->format('Y-m-d') }}</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="label">الهاتف</div>
                    <div class="value">{{ $quotation->lead->phone ?? '-' }}</div>
                </td>
                <td>
                    <div class="label">صالح حتى</div>
                    <div class="value">{{ $quotation->valid_until ? $quotation->valid_until->format('Y-m-d') : '-' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">{{ $quotation->title }}</div>
    @if($quotation->description)
        <p style="margin-bottom: 15px; color: #4b5563;">{{ $quotation->description }}</p>
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
    <div class="terms">
        <h4>الشروط والأحكام:</h4>
        <p>{{ $quotation->terms_conditions }}</p>
    </div>
    @endif

    <div class="footer">
        <p>شكراً لثقتكم بنا - حازم عبدالله للتشطيبات</p>
        <p>تم إنشاء هذا المستند بتاريخ {{ now()->format('Y-m-d') }}</p>
    </div>
</body>
</html>

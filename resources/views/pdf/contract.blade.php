<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; direction: rtl; font-size: 12px; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #1a56db; }
        .section { margin-bottom: 20px; }
        .section h3 { color: #1a56db; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px; }
        .terms-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .terms-table th, .terms-table td { border: 1px solid #ddd; padding: 8px; text-align: right; }
        .terms-table th { background-color: #f3f4f6; }
        .signatures { margin-top: 50px; }
        .signature-box { display: inline-block; width: 45%; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>عقد تشطيبات</h1>
        <p>رقم العقد: {{ $contract->contract_number }}</p>
    </div>

    <div class="section">
        <h3>بيانات العقد</h3>
        <table class="info-table">
            <tr>
                <td><strong>العميل:</strong> {{ $contract->project->lead->name }}</td>
                <td><strong>المشروع:</strong> {{ $contract->project->name }}</td>
            </tr>
            <tr>
                <td><strong>تاريخ البدء:</strong> {{ $contract->start_date->format('Y-m-d') }}</td>
                <td><strong>تاريخ الانتهاء:</strong> {{ $contract->end_date->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>قيمة العقد:</strong> {{ number_format($contract->total_value, 2) }} ج.م</td>
            </tr>
        </table>
    </div>

    @if($contract->scope_of_work)
    <div class="section">
        <h3>نطاق العمل</h3>
        <p>{{ $contract->scope_of_work }}</p>
    </div>
    @endif

    @if($contract->paymentTerms->count() > 0)
    <div class="section">
        <h3>شروط الدفع</h3>
        <table class="terms-table">
            <thead>
                <tr>
                    <th>الوصف</th>
                    <th>النسبة</th>
                    <th>المبلغ</th>
                    <th>تاريخ الاستحقاق</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contract->paymentTerms as $term)
                <tr>
                    <td>{{ $term->description }}</td>
                    <td>{{ $term->percentage }}%</td>
                    <td>{{ number_format($term->amount, 2) }} ج.م</td>
                    <td>{{ $term->due_date?->format('Y-m-d') ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($contract->terms_conditions)
    <div class="section">
        <h3>الشروط والأحكام</h3>
        <p>{{ $contract->terms_conditions }}</p>
    </div>
    @endif

    <div class="signatures">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%; text-align: center;">
                    <p><strong>الطرف الأول (الشركة)</strong></p>
                    <p>التوقيع: _________________</p>
                    <p>التاريخ: _________________</p>
                </td>
                <td style="width: 50%; text-align: center;">
                    <p><strong>الطرف الثاني (العميل)</strong></p>
                    <p>التوقيع: _________________</p>
                    <p>التاريخ: _________________</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>

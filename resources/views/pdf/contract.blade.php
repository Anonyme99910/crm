<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        * { font-family: 'DejaVu Sans', sans-serif; }
        body { direction: rtl; font-size: 12px; line-height: 1.6; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #d4af37; }
        .header .company { font-size: 20px; color: #d4af37; font-weight: bold; margin-bottom: 5px; }
        .header h1 { color: #1f2937; margin: 10px 0; font-size: 24px; }
        .header .number { color: #6b7280; font-size: 14px; }
        .section { margin-bottom: 25px; }
        .section h3 { color: #1f2937; border-bottom: 2px solid #d4af37; padding-bottom: 8px; margin-bottom: 15px; font-size: 14px; }
        .info-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px; margin-bottom: 20px; }
        .info-table { width: 100%; margin-bottom: 15px; }
        .info-table td { padding: 8px 5px; vertical-align: top; }
        .info-table .label { color: #6b7280; font-size: 11px; }
        .info-table .value { font-weight: bold; font-size: 13px; }
        .terms-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .terms-table th { background: linear-gradient(135deg, #1f2937 0%, #374151 100%); color: white; padding: 12px 8px; text-align: right; font-size: 11px; }
        .terms-table td { border: 1px solid #e5e7eb; padding: 10px 8px; text-align: right; }
        .terms-table tr:nth-child(even) { background-color: #f9fafb; }
        .scope-box { background: #f0fdf4; border: 1px solid #86efac; border-radius: 8px; padding: 15px; }
        .terms-box { background: #fffbeb; border: 1px solid #fcd34d; border-radius: 8px; padding: 15px; }
        .signatures { margin-top: 50px; border-top: 1px solid #e5e7eb; padding-top: 30px; }
        .footer { margin-top: 40px; text-align: center; color: #9ca3af; font-size: 10px; border-top: 1px solid #e5e7eb; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">حازم عبدالله للتشطيبات</div>
        <h1>عقد تشطيبات</h1>
        <div class="number">رقم العقد: {{ $contract->contract_number }}</div>
    </div>

    <div class="section">
        <h3>بيانات العقد</h3>
        <div class="info-box">
            <table class="info-table">
                <tr>
                    <td width="50%">
                        <div class="label">العميل</div>
                        <div class="value">{{ $contract->project->lead->name ?? '-' }}</div>
                    </td>
                    <td width="50%">
                        <div class="label">المشروع</div>
                        <div class="value">{{ $contract->project->name ?? '-' }}</div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="label">تاريخ البدء</div>
                        <div class="value">{{ $contract->start_date ? $contract->start_date->format('Y-m-d') : '-' }}</div>
                    </td>
                    <td>
                        <div class="label">تاريخ الانتهاء</div>
                        <div class="value">{{ $contract->end_date ? $contract->end_date->format('Y-m-d') : '-' }}</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <div class="label">قيمة العقد</div>
                        <div class="value" style="font-size: 18px; color: #d4af37;">{{ number_format($contract->total_value, 2) }} ج.م</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    @if($contract->scope_of_work)
    <div class="section">
        <h3>نطاق العمل</h3>
        <div class="scope-box">
            <p style="margin: 0; white-space: pre-line;">{{ $contract->scope_of_work }}</p>
        </div>
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
        <div class="terms-box">
            <p style="margin: 0; white-space: pre-line;">{{ $contract->terms_conditions }}</p>
        </div>
    </div>
    @endif

    <div class="signatures">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%; text-align: center; padding: 20px;">
                    <p><strong>الطرف الأول (الشركة)</strong></p>
                    <p style="margin-top: 40px;">التوقيع: _________________</p>
                    <p>التاريخ: _________________</p>
                </td>
                <td style="width: 50%; text-align: center; padding: 20px;">
                    <p><strong>الطرف الثاني (العميل)</strong></p>
                    <p style="margin-top: 40px;">التوقيع: _________________</p>
                    <p>التاريخ: _________________</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>حازم عبدالله للتشطيبات - تم إنشاء هذا المستند بتاريخ {{ now()->format('Y-m-d') }}</p>
    </div>
</body>
</html>

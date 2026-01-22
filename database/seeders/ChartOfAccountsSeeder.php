<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChartOfAccount;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // Assets (1000 Series)
            ['account_code' => '1000', 'account_name' => 'Assets', 'account_name_ar' => 'الأصول', 'account_type' => 'asset', 'normal_balance' => 'debit', 'is_header' => true, 'is_system' => true, 'level' => 1],
            ['account_code' => '1100', 'account_name' => 'Cash and Bank', 'account_name_ar' => 'النقد والبنوك', 'account_type' => 'asset', 'normal_balance' => 'debit', 'is_header' => true, 'parent_code' => '1000', 'level' => 2],
            ['account_code' => '1110', 'account_name' => 'Cash on Hand', 'account_name_ar' => 'النقد في الصندوق', 'account_type' => 'asset', 'normal_balance' => 'debit', 'parent_code' => '1100', 'level' => 3],
            ['account_code' => '1120', 'account_name' => 'Bank Accounts', 'account_name_ar' => 'الحسابات البنكية', 'account_type' => 'asset', 'normal_balance' => 'debit', 'parent_code' => '1100', 'level' => 3],
            ['account_code' => '1130', 'account_name' => 'Petty Cash', 'account_name_ar' => 'صندوق المصروفات النثرية', 'account_type' => 'asset', 'normal_balance' => 'debit', 'parent_code' => '1100', 'level' => 3],
            ['account_code' => '1200', 'account_name' => 'Accounts Receivable', 'account_name_ar' => 'الذمم المدينة', 'account_type' => 'asset', 'normal_balance' => 'debit', 'is_header' => true, 'parent_code' => '1000', 'level' => 2],
            ['account_code' => '1210', 'account_name' => 'Trade Receivables', 'account_name_ar' => 'ذمم العملاء', 'account_type' => 'asset', 'normal_balance' => 'debit', 'parent_code' => '1200', 'level' => 3],
            ['account_code' => '1220', 'account_name' => 'Employee Advances', 'account_name_ar' => 'سلف الموظفين', 'account_type' => 'asset', 'normal_balance' => 'debit', 'parent_code' => '1200', 'level' => 3],
            ['account_code' => '1230', 'account_name' => 'Other Receivables', 'account_name_ar' => 'ذمم أخرى', 'account_type' => 'asset', 'normal_balance' => 'debit', 'parent_code' => '1200', 'level' => 3],
            ['account_code' => '1300', 'account_name' => 'Inventory', 'account_name_ar' => 'المخزون', 'account_type' => 'asset', 'normal_balance' => 'debit', 'is_header' => true, 'parent_code' => '1000', 'level' => 2],
            ['account_code' => '1310', 'account_name' => 'Raw Materials', 'account_name_ar' => 'المواد الخام', 'account_type' => 'asset', 'normal_balance' => 'debit', 'parent_code' => '1300', 'level' => 3],
            ['account_code' => '1320', 'account_name' => 'Work in Progress', 'account_name_ar' => 'أعمال تحت التنفيذ', 'account_type' => 'asset', 'normal_balance' => 'debit', 'parent_code' => '1300', 'level' => 3],
            ['account_code' => '1400', 'account_name' => 'Fixed Assets', 'account_name_ar' => 'الأصول الثابتة', 'account_type' => 'asset', 'normal_balance' => 'debit', 'is_header' => true, 'parent_code' => '1000', 'level' => 2],
            ['account_code' => '1410', 'account_name' => 'Vehicles', 'account_name_ar' => 'المركبات', 'account_type' => 'asset', 'normal_balance' => 'debit', 'parent_code' => '1400', 'level' => 3],
            ['account_code' => '1420', 'account_name' => 'Equipment', 'account_name_ar' => 'المعدات', 'account_type' => 'asset', 'normal_balance' => 'debit', 'parent_code' => '1400', 'level' => 3],
            ['account_code' => '1430', 'account_name' => 'Furniture', 'account_name_ar' => 'الأثاث', 'account_type' => 'asset', 'normal_balance' => 'debit', 'parent_code' => '1400', 'level' => 3],
            ['account_code' => '1490', 'account_name' => 'Accumulated Depreciation', 'account_name_ar' => 'مجمع الإهلاك', 'account_type' => 'asset', 'normal_balance' => 'credit', 'parent_code' => '1400', 'level' => 3],

            // Liabilities (2000 Series)
            ['account_code' => '2000', 'account_name' => 'Liabilities', 'account_name_ar' => 'الالتزامات', 'account_type' => 'liability', 'normal_balance' => 'credit', 'is_header' => true, 'is_system' => true, 'level' => 1],
            ['account_code' => '2100', 'account_name' => 'Accounts Payable', 'account_name_ar' => 'الذمم الدائنة', 'account_type' => 'liability', 'normal_balance' => 'credit', 'is_header' => true, 'parent_code' => '2000', 'level' => 2],
            ['account_code' => '2110', 'account_name' => 'Trade Payables', 'account_name_ar' => 'ذمم الموردين', 'account_type' => 'liability', 'normal_balance' => 'credit', 'parent_code' => '2100', 'level' => 3],
            ['account_code' => '2120', 'account_name' => 'Accrued Expenses', 'account_name_ar' => 'مصروفات مستحقة', 'account_type' => 'liability', 'normal_balance' => 'credit', 'parent_code' => '2100', 'level' => 3],
            ['account_code' => '2200', 'account_name' => 'Taxes Payable', 'account_name_ar' => 'الضرائب المستحقة', 'account_type' => 'liability', 'normal_balance' => 'credit', 'is_header' => true, 'parent_code' => '2000', 'level' => 2],
            ['account_code' => '2210', 'account_name' => 'VAT Payable', 'account_name_ar' => 'ضريبة القيمة المضافة', 'account_type' => 'liability', 'normal_balance' => 'credit', 'parent_code' => '2200', 'level' => 3],
            ['account_code' => '2300', 'account_name' => 'Customer Deposits', 'account_name_ar' => 'دفعات العملاء المقدمة', 'account_type' => 'liability', 'normal_balance' => 'credit', 'parent_code' => '2000', 'level' => 2],
            ['account_code' => '2400', 'account_name' => 'Loans Payable', 'account_name_ar' => 'القروض', 'account_type' => 'liability', 'normal_balance' => 'credit', 'parent_code' => '2000', 'level' => 2],

            // Equity (3000 Series)
            ['account_code' => '3000', 'account_name' => 'Equity', 'account_name_ar' => 'حقوق الملكية', 'account_type' => 'equity', 'normal_balance' => 'credit', 'is_header' => true, 'is_system' => true, 'level' => 1],
            ['account_code' => '3100', 'account_name' => 'Capital', 'account_name_ar' => 'رأس المال', 'account_type' => 'equity', 'normal_balance' => 'credit', 'parent_code' => '3000', 'level' => 2],
            ['account_code' => '3200', 'account_name' => 'Retained Earnings', 'account_name_ar' => 'الأرباح المحتجزة', 'account_type' => 'equity', 'normal_balance' => 'credit', 'parent_code' => '3000', 'level' => 2],
            ['account_code' => '3300', 'account_name' => 'Current Year Earnings', 'account_name_ar' => 'أرباح السنة الحالية', 'account_type' => 'equity', 'normal_balance' => 'credit', 'parent_code' => '3000', 'level' => 2],

            // Revenue (4000 Series)
            ['account_code' => '4000', 'account_name' => 'Revenue', 'account_name_ar' => 'الإيرادات', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'is_header' => true, 'is_system' => true, 'level' => 1],
            ['account_code' => '4100', 'account_name' => 'Project Revenue', 'account_name_ar' => 'إيرادات المشاريع', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'is_header' => true, 'parent_code' => '4000', 'level' => 2],
            ['account_code' => '4110', 'account_name' => 'Design Services', 'account_name_ar' => 'خدمات التصميم', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'parent_code' => '4100', 'level' => 3],
            ['account_code' => '4120', 'account_name' => 'Execution Services', 'account_name_ar' => 'خدمات التنفيذ', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'parent_code' => '4100', 'level' => 3],
            ['account_code' => '4130', 'account_name' => 'Supervision Fees', 'account_name_ar' => 'رسوم الإشراف', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'parent_code' => '4100', 'level' => 3],
            ['account_code' => '4200', 'account_name' => 'Other Income', 'account_name_ar' => 'إيرادات أخرى', 'account_type' => 'revenue', 'normal_balance' => 'credit', 'parent_code' => '4000', 'level' => 2],

            // Direct Costs (5000 Series)
            ['account_code' => '5000', 'account_name' => 'Direct Costs', 'account_name_ar' => 'التكاليف المباشرة', 'account_type' => 'expense', 'normal_balance' => 'debit', 'is_header' => true, 'is_system' => true, 'level' => 1],
            ['account_code' => '5100', 'account_name' => 'Materials Cost', 'account_name_ar' => 'تكلفة المواد', 'account_type' => 'expense', 'normal_balance' => 'debit', 'parent_code' => '5000', 'level' => 2],
            ['account_code' => '5200', 'account_name' => 'Labor Cost', 'account_name_ar' => 'تكلفة العمالة', 'account_type' => 'expense', 'normal_balance' => 'debit', 'parent_code' => '5000', 'level' => 2],
            ['account_code' => '5300', 'account_name' => 'Subcontractor Cost', 'account_name_ar' => 'تكلفة المقاولين', 'account_type' => 'expense', 'normal_balance' => 'debit', 'parent_code' => '5000', 'level' => 2],
            ['account_code' => '5400', 'account_name' => 'Equipment Rental', 'account_name_ar' => 'إيجار المعدات', 'account_type' => 'expense', 'normal_balance' => 'debit', 'parent_code' => '5000', 'level' => 2],

            // Indirect Costs (6000 Series)
            ['account_code' => '6000', 'account_name' => 'Operating Expenses', 'account_name_ar' => 'المصروفات التشغيلية', 'account_type' => 'expense', 'normal_balance' => 'debit', 'is_header' => true, 'is_system' => true, 'level' => 1],
            ['account_code' => '6100', 'account_name' => 'Salaries & Wages', 'account_name_ar' => 'الرواتب والأجور', 'account_type' => 'expense', 'normal_balance' => 'debit', 'parent_code' => '6000', 'level' => 2],
            ['account_code' => '6200', 'account_name' => 'Rent Expense', 'account_name_ar' => 'مصروف الإيجار', 'account_type' => 'expense', 'normal_balance' => 'debit', 'parent_code' => '6000', 'level' => 2],
            ['account_code' => '6300', 'account_name' => 'Utilities', 'account_name_ar' => 'المرافق', 'account_type' => 'expense', 'normal_balance' => 'debit', 'parent_code' => '6000', 'level' => 2],
            ['account_code' => '6400', 'account_name' => 'Marketing & Advertising', 'account_name_ar' => 'التسويق والإعلان', 'account_type' => 'expense', 'normal_balance' => 'debit', 'parent_code' => '6000', 'level' => 2],
            ['account_code' => '6500', 'account_name' => 'Office Supplies', 'account_name_ar' => 'مستلزمات المكتب', 'account_type' => 'expense', 'normal_balance' => 'debit', 'parent_code' => '6000', 'level' => 2],
            ['account_code' => '6600', 'account_name' => 'Transportation', 'account_name_ar' => 'النقل والمواصلات', 'account_type' => 'expense', 'normal_balance' => 'debit', 'parent_code' => '6000', 'level' => 2],
            ['account_code' => '6700', 'account_name' => 'Insurance', 'account_name_ar' => 'التأمين', 'account_type' => 'expense', 'normal_balance' => 'debit', 'parent_code' => '6000', 'level' => 2],
            ['account_code' => '6800', 'account_name' => 'Depreciation', 'account_name_ar' => 'الإهلاك', 'account_type' => 'expense', 'normal_balance' => 'debit', 'parent_code' => '6000', 'level' => 2],
            ['account_code' => '6900', 'account_name' => 'Other Expenses', 'account_name_ar' => 'مصروفات أخرى', 'account_type' => 'expense', 'normal_balance' => 'debit', 'parent_code' => '6000', 'level' => 2],

            // Financial Expenses (7000 Series)
            ['account_code' => '7000', 'account_name' => 'Financial Expenses', 'account_name_ar' => 'المصروفات المالية', 'account_type' => 'expense', 'normal_balance' => 'debit', 'is_header' => true, 'is_system' => true, 'level' => 1],
            ['account_code' => '7100', 'account_name' => 'Bank Charges', 'account_name_ar' => 'رسوم بنكية', 'account_type' => 'expense', 'normal_balance' => 'debit', 'parent_code' => '7000', 'level' => 2],
            ['account_code' => '7200', 'account_name' => 'Interest Expense', 'account_name_ar' => 'مصروف الفوائد', 'account_type' => 'expense', 'normal_balance' => 'debit', 'parent_code' => '7000', 'level' => 2],
            ['account_code' => '7300', 'account_name' => 'Foreign Exchange Loss', 'account_name_ar' => 'خسائر صرف العملات', 'account_type' => 'expense', 'normal_balance' => 'debit', 'parent_code' => '7000', 'level' => 2],
        ];

        $parentIds = [];

        foreach ($accounts as $account) {
            $parentId = null;
            if (isset($account['parent_code'])) {
                $parentId = $parentIds[$account['parent_code']] ?? null;
                unset($account['parent_code']);
            }

            $created = ChartOfAccount::create(array_merge($account, ['parent_id' => $parentId]));
            $parentIds[$account['account_code']] = $created->id;
        }
    }
}

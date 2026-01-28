<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Contract;
use App\Models\Task;
use App\Models\SiteVisit;
use App\Models\Material;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\CustomerInvoice;
use App\Models\CustomerInvoiceItem;
use App\Models\SupplierBill;
use App\Models\AuditLog;
use App\Models\LoginAttempt;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class MockDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@crm.com'],
            ['name' => 'مدير النظام', 'password' => Hash::make('password'), 'role' => 'admin']
        );

        $manager = User::firstOrCreate(
            ['email' => 'manager@crm.com'],
            ['name' => 'أحمد محمد', 'password' => Hash::make('password'), 'role' => 'manager']
        );

        $engineer = User::firstOrCreate(
            ['email' => 'engineer@crm.com'],
            ['name' => 'محمد علي', 'password' => Hash::make('password'), 'role' => 'engineer']
        );

        $accountant = User::firstOrCreate(
            ['email' => 'accountant@crm.com'],
            ['name' => 'سارة أحمد', 'password' => Hash::make('password'), 'role' => 'accountant']
        );

        // Create Leads
        $leads = [];
        $leadData = [
            ['name' => 'محمد أحمد السيد', 'phone' => '01012345678', 'email' => 'mohamed@example.com', 'address' => 'القاهرة، مدينة نصر', 'source' => 'website', 'status' => 'hot', 'stage' => 'qualified'],
            ['name' => 'أحمد محمود علي', 'phone' => '01123456789', 'email' => 'ahmed@example.com', 'address' => 'الجيزة، الدقي', 'source' => 'referral', 'status' => 'warm', 'stage' => 'new'],
            ['name' => 'فاطمة حسن محمد', 'phone' => '01234567890', 'email' => 'fatma@example.com', 'address' => 'الإسكندرية، سموحة', 'source' => 'website', 'status' => 'cold', 'stage' => 'contacted'],
            ['name' => 'علي عبدالله إبراهيم', 'phone' => '01098765432', 'email' => 'ali@example.com', 'address' => 'القاهرة، التجمع الخامس', 'source' => 'call', 'status' => 'hot', 'stage' => 'qualified'],
            ['name' => 'نورا سعيد أحمد', 'phone' => '01187654321', 'email' => 'noura@example.com', 'address' => 'الجيزة، الشيخ زايد', 'source' => 'website', 'status' => 'warm', 'stage' => 'proposal'],
        ];

        foreach ($leadData as $data) {
            $leads[] = Lead::firstOrCreate(['phone' => $data['phone']], array_merge($data, ['assigned_to' => $manager->id]));
        }

        // Create Projects
        $projects = [];
        $projectData = [
            ['name' => 'فيلا التجمع الخامس', 'address' => 'القاهرة، التجمع الخامس', 'status' => 'in_progress', 'contract_value' => 850000, 'start_date' => Carbon::now()->subMonths(2)],
            ['name' => 'شقة مدينة نصر', 'address' => 'القاهرة، مدينة نصر', 'status' => 'pending', 'contract_value' => 320000, 'start_date' => Carbon::now()->subWeeks(2)],
            ['name' => 'مكتب الدقي', 'address' => 'الجيزة، الدقي', 'status' => 'completed', 'contract_value' => 180000, 'start_date' => Carbon::now()->subMonths(4)],
            ['name' => 'فيلا الشيخ زايد', 'address' => 'الجيزة، الشيخ زايد', 'status' => 'in_progress', 'contract_value' => 1200000, 'start_date' => Carbon::now()->subMonths(1)],
        ];

        foreach ($projectData as $index => $data) {
            $lead = $leads[$index % count($leads)];
            $projects[] = Project::firstOrCreate(
                ['name' => $data['name']],
                array_merge($data, ['lead_id' => $lead->id, 'manager_id' => $manager->id])
            );
        }

        // Create Site Visits
        foreach ($leads as $index => $lead) {
            if ($index < 3) {
                SiteVisit::firstOrCreate(
                    ['lead_id' => $lead->id, 'scheduled_at' => Carbon::now()->addDays($index + 1)],
                    [
                        'engineer_id' => $engineer->id,
                        'address' => $lead->address,
                        'status' => $index === 0 ? 'scheduled' : ($index === 1 ? 'completed' : 'scheduled'),
                        'client_requirements' => 'تشطيب كامل للشقة مع أعمال الكهرباء والسباكة',
                        'notes' => 'العميل يفضل التصميم الحديث',
                        'created_by' => $manager->id
                    ]
                );
            }
        }

        // Create Quotations
        foreach ($leads as $index => $lead) {
            if ($index < 3) {
                $quotation = Quotation::firstOrCreate(
                    ['lead_id' => $lead->id],
                    [
                        'quotation_number' => 'QT-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                        'title' => 'عرض سعر تشطيب - ' . $lead->name,
                        'status' => $index === 0 ? 'sent' : ($index === 1 ? 'accepted' : 'draft'),
                        'valid_until' => Carbon::now()->addDays(30),
                        'subtotal' => 100000 + ($index * 50000),
                        'tax_rate' => 14,
                        'tax_amount' => (100000 + ($index * 50000)) * 0.14,
                        'total' => (100000 + ($index * 50000)) * 1.14,
                        'terms_conditions' => 'الأسعار شاملة المواد والعمالة. مدة التنفيذ 60 يوم.',
                        'created_by' => $manager->id
                    ]
                );

                // Add quotation items
                $items = [
                    ['name' => 'أعمال الدهانات', 'unit' => 'متر مربع', 'quantity' => 200, 'unit_price' => 150],
                    ['name' => 'أعمال السيراميك', 'unit' => 'متر مربع', 'quantity' => 100, 'unit_price' => 250],
                    ['name' => 'أعمال الكهرباء', 'unit' => 'نقطة', 'quantity' => 50, 'unit_price' => 200],
                    ['name' => 'أعمال السباكة', 'unit' => 'نقطة', 'quantity' => 30, 'unit_price' => 300],
                ];

                foreach ($items as $item) {
                    QuotationItem::firstOrCreate(
                        ['quotation_id' => $quotation->id, 'name' => $item['name']],
                        array_merge($item, ['total' => $item['quantity'] * $item['unit_price']])
                    );
                }
            }
        }

        // Create Contracts
        foreach ($projects as $index => $project) {
            if ($index < 2) {
                Contract::firstOrCreate(
                    ['project_id' => $project->id],
                    [
                        'contract_number' => 'CON-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                        'title' => 'عقد تشطيب - ' . $project->name,
                        'total_value' => $project->contract_value,
                        'status' => $index === 0 ? 'active' : 'draft',
                        'start_date' => $project->start_date,
                        'end_date' => Carbon::parse($project->start_date)->addMonths(3),
                        'scope_of_work' => 'تشطيب كامل يشمل الدهانات، السيراميك، الكهرباء، السباكة، والنجارة',
                        'terms_conditions' => 'يتم الدفع على 4 دفعات حسب مراحل التنفيذ',
                        'created_by' => $manager->id
                    ]
                );
            }
        }

        // Create Tasks
        $taskData = [
            ['title' => 'معاينة موقع فيلا التجمع', 'priority' => 'high', 'status' => 'completed'],
            ['title' => 'إعداد عرض سعر شقة مدينة نصر', 'priority' => 'urgent', 'status' => 'in_progress'],
            ['title' => 'متابعة توريد السيراميك', 'priority' => 'medium', 'status' => 'pending'],
            ['title' => 'اجتماع مع العميل', 'priority' => 'high', 'status' => 'pending'],
            ['title' => 'مراجعة فواتير الموردين', 'priority' => 'low', 'status' => 'completed'],
        ];

        foreach ($taskData as $index => $data) {
            Task::firstOrCreate(
                ['title' => $data['title']],
                array_merge($data, [
                    'project_id' => $projects[array_rand($projects)]->id,
                    'assigned_to' => [$engineer->id, $manager->id, $accountant->id][array_rand([$engineer->id, $manager->id, $accountant->id])],
                    'due_date' => Carbon::now()->addDays(rand(1, 14)),
                    'description' => 'وصف المهمة التفصيلي',
                    'created_by' => $manager->id
                ])
            );
        }

        // Create Materials
        $materials = [];
        $materialData = [
            ['code' => 'MAT-001', 'name' => 'سيراميك أرضيات 60x60', 'unit' => 'متر مربع', 'unit_price' => 180, 'current_stock' => 500, 'minimum_stock' => 100],
            ['code' => 'MAT-002', 'name' => 'دهان بلاستيك أبيض', 'unit' => 'جالون', 'unit_price' => 350, 'current_stock' => 50, 'minimum_stock' => 20],
            ['code' => 'MAT-003', 'name' => 'أسلاك كهرباء 2.5 مم', 'unit' => 'متر', 'unit_price' => 15, 'current_stock' => 2000, 'minimum_stock' => 500],
            ['code' => 'MAT-004', 'name' => 'مواسير PVC 4 بوصة', 'unit' => 'متر', 'unit_price' => 45, 'current_stock' => 200, 'minimum_stock' => 50],
            ['code' => 'MAT-005', 'name' => 'جبس بورد', 'unit' => 'لوح', 'unit_price' => 120, 'current_stock' => 30, 'minimum_stock' => 50],
        ];

        foreach ($materialData as $data) {
            $materials[] = Material::firstOrCreate(['code' => $data['code']], $data);
        }

        // Create Suppliers
        $suppliers = [];
        $supplierData = [
            ['name' => 'شركة السيراميك المصرية', 'type' => 'supplier', 'phone' => '0223456789', 'specialization' => 'سيراميك وبورسلين'],
            ['name' => 'مصنع الدهانات الوطنية', 'type' => 'supplier', 'phone' => '0234567890', 'specialization' => 'دهانات ومواد عازلة'],
            ['name' => 'مؤسسة الكهرباء الحديثة', 'type' => 'supplier', 'phone' => '0245678901', 'specialization' => 'مستلزمات كهربائية'],
            ['name' => 'شركة المقاولات المتحدة', 'type' => 'contractor', 'phone' => '0256789012', 'specialization' => 'أعمال البناء والتشطيب'],
        ];

        foreach ($supplierData as $index => $data) {
            $suppliers[] = Supplier::firstOrCreate(
                ['phone' => $data['phone']],
                array_merge($data, ['code' => 'SUP-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT), 'rating' => rand(3, 5)])
            );
        }

        // Create Purchase Orders
        foreach ($suppliers as $index => $supplier) {
            if ($index < 2) {
                $po = PurchaseOrder::firstOrCreate(
                    ['supplier_id' => $supplier->id, 'po_number' => 'PO-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT)],
                    [
                        'project_id' => $projects[array_rand($projects)]->id,
                        'status' => $index === 0 ? 'approved' : 'draft',
                        'expected_delivery' => Carbon::now()->addDays(rand(5, 15)),
                        'subtotal' => 50000,
                        'tax_amount' => 7000,
                        'total' => 57000,
                        'created_by' => $manager->id
                    ]
                );

                // Add PO items
                PurchaseOrderItem::firstOrCreate(
                    ['purchase_order_id' => $po->id, 'material_id' => $materials[array_rand($materials)]->id],
                    ['quantity' => rand(50, 200), 'unit_price' => rand(100, 500), 'total' => rand(5000, 50000)]
                );
            }
        }

        // Create Bank Accounts
        $bankAccounts = [];
        $bankData = [
            ['account_name' => 'الحساب الرئيسي', 'bank_name' => 'البنك الأهلي المصري', 'account_number' => '1234567890', 'account_type' => 'checking', 'currency' => 'EGP', 'opening_balance' => 500000, 'current_balance' => 485000, 'is_default' => true],
            ['account_name' => 'حساب المصروفات', 'bank_name' => 'بنك مصر', 'account_number' => '0987654321', 'account_type' => 'checking', 'currency' => 'EGP', 'opening_balance' => 100000, 'current_balance' => 75000, 'is_default' => false],
            ['account_name' => 'الصندوق النقدي', 'bank_name' => 'نقدي', 'account_number' => 'CASH-001', 'account_type' => 'cash', 'currency' => 'EGP', 'opening_balance' => 50000, 'current_balance' => 35000, 'is_default' => false],
        ];

        foreach ($bankData as $data) {
            $bankAccounts[] = BankAccount::firstOrCreate(['account_number' => $data['account_number']], array_merge($data, ['is_active' => true]));
        }

        // Create Bank Transactions
        foreach ($bankAccounts as $account) {
            for ($i = 0; $i < 3; $i++) {
                BankTransaction::firstOrCreate(
                    ['bank_account_id' => $account->id, 'reference_number' => 'TXN-' . $account->id . '-' . ($i + 1)],
                    [
                        'type' => $i % 2 === 0 ? 'deposit' : 'withdrawal',
                        'amount' => rand(5000, 20000),
                        'balance_after' => $account->current_balance,
                        'description' => $i % 2 === 0 ? 'إيداع من عميل' : 'سحب لمصروفات',
                        'transaction_date' => Carbon::now()->subDays(rand(1, 30)),
                        'created_by' => $accountant->id
                    ]
                );
            }
        }

        // Create Customer Invoices
        foreach ($projects as $index => $project) {
            if ($index < 2) {
                $subtotal = $project->contract_value * 0.25;
                $taxAmount = $subtotal * 0.14;
                $totalAmount = $subtotal + $taxAmount;
                
                $invoice = CustomerInvoice::firstOrCreate(
                    ['project_id' => $project->id, 'invoice_number' => 'INV-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT)],
                    [
                        'client_id' => $leads[$index % count($leads)]->id,
                        'invoice_type' => 'progress',
                        'invoice_date' => Carbon::now()->subDays(rand(5, 20)),
                        'due_date' => Carbon::now()->addDays(rand(10, 30)),
                        'subtotal' => $subtotal,
                        'tax_amount' => $taxAmount,
                        'total_amount' => $totalAmount,
                        'paid_amount' => $index === 0 ? $totalAmount : 0,
                        'balance' => $index === 0 ? 0 : $totalAmount,
                        'status' => $index === 0 ? 'paid' : 'draft',
                        'completion_percentage' => 25,
                        'created_by' => $accountant->id
                    ]
                );

                CustomerInvoiceItem::firstOrCreate(
                    ['customer_invoice_id' => $invoice->id, 'description' => 'مستخلص أعمال المرحلة الأولى'],
                    ['quantity' => 1, 'unit_price' => $subtotal, 'tax_rate' => 14, 'tax_amount' => $taxAmount, 'total_amount' => $totalAmount]
                );
            }
        }

        // Create Supplier Bills
        foreach ($suppliers as $index => $supplier) {
            if ($index < 2) {
                $subtotal = rand(20000, 80000);
                $taxAmount = $subtotal * 0.14;
                $totalAmount = $subtotal + $taxAmount;
                
                $bill = SupplierBill::firstOrCreate(
                    ['supplier_id' => $supplier->id, 'bill_number' => 'BILL-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT)],
                    [
                        'project_id' => $projects[$index % count($projects)]->id,
                        'supplier_invoice_number' => 'SUP-INV-' . rand(1000, 9999),
                        'bill_date' => Carbon::now()->subDays(rand(5, 15)),
                        'due_date' => Carbon::now()->addDays(rand(15, 45)),
                        'subtotal' => $subtotal,
                        'tax_amount' => $taxAmount,
                        'total_amount' => $totalAmount,
                        'paid_amount' => $index === 0 ? $totalAmount : 0,
                        'balance' => $index === 0 ? 0 : $totalAmount,
                        'status' => $index === 0 ? 'paid' : 'pending_approval',
                        'created_by' => $accountant->id
                    ]
                );
            }
        }

        // Create Audit Logs
        $auditActions = ['create', 'update', 'delete', 'login', 'logout', 'export'];
        $models = ['Lead', 'Project', 'Quotation', 'Invoice', 'Task'];
        
        for ($i = 0; $i < 20; $i++) {
            AuditLog::firstOrCreate(
                ['id' => 1000 + $i],
                [
                    'user_id' => [$admin->id, $manager->id, $engineer->id, $accountant->id][array_rand([$admin->id, $manager->id, $engineer->id, $accountant->id])],
                    'action' => $auditActions[array_rand($auditActions)],
                    'model_type' => 'App\\Models\\' . $models[array_rand($models)],
                    'model_id' => rand(1, 10),
                    'old_values' => json_encode(['status' => 'old_value']),
                    'new_values' => json_encode(['status' => 'new_value']),
                    'ip_address' => '192.168.1.' . rand(1, 255),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    'created_at' => Carbon::now()->subHours(rand(1, 168))
                ]
            );
        }

        // Create Login Attempts
        for ($i = 0; $i < 15; $i++) {
            LoginAttempt::firstOrCreate(
                ['id' => 1000 + $i],
                [
                    'user_id' => $i < 12 ? [$admin->id, $manager->id, $engineer->id, $accountant->id][array_rand([$admin->id, $manager->id, $engineer->id, $accountant->id])] : null,
                    'email' => $i < 12 ? ['admin@crm.com', 'manager@crm.com', 'engineer@crm.com'][array_rand(['admin@crm.com', 'manager@crm.com', 'engineer@crm.com'])] : 'hacker@test.com',
                    'ip_address' => '192.168.1.' . rand(1, 255),
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    'successful' => $i < 12,
                    'failure_reason' => $i >= 12 ? 'invalid_credentials' : null,
                    'created_at' => Carbon::now()->subHours(rand(1, 72))
                ]
            );
        }

        $this->command->info('Mock data seeded successfully!');
    }
}

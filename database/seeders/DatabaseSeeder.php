<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Lead;
use App\Models\MaterialCategory;
use App\Models\Material;
use App\Models\QuotationTemplate;
use App\Models\QualityChecklist;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'مدير النظام',
            'email' => 'admin@crm.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '01000000000',
        ]);

        // Create Manager
        User::create([
            'name' => 'أحمد محمد',
            'email' => 'manager@crm.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'phone' => '01100000000',
        ]);

        // Create Sales
        User::create([
            'name' => 'محمد علي',
            'email' => 'sales@crm.com',
            'password' => Hash::make('password'),
            'role' => 'sales',
            'phone' => '01200000000',
        ]);

        // Create Engineer
        User::create([
            'name' => 'م. خالد أحمد',
            'email' => 'engineer@crm.com',
            'password' => Hash::make('password'),
            'role' => 'engineer',
            'phone' => '01500000000',
        ]);

        // Material Categories
        $categories = [
            ['name' => 'دهانات', 'slug' => 'paints'],
            ['name' => 'سيراميك وبورسلين', 'slug' => 'ceramics'],
            ['name' => 'أدوات صحية', 'slug' => 'sanitary'],
            ['name' => 'كهرباء', 'slug' => 'electrical'],
            ['name' => 'نجارة', 'slug' => 'carpentry'],
            ['name' => 'ألوميتال', 'slug' => 'aluminum'],
            ['name' => 'جبس', 'slug' => 'gypsum'],
        ];

        foreach ($categories as $cat) {
            MaterialCategory::create($cat);
        }

        // Sample Materials
        $materials = [
            ['code' => 'PNT-001', 'name' => 'دهان جوتن داخلي', 'unit' => 'جالون', 'unit_price' => 450, 'category_id' => 1],
            ['code' => 'PNT-002', 'name' => 'دهان جوتن خارجي', 'unit' => 'جالون', 'unit_price' => 550, 'category_id' => 1],
            ['code' => 'CRM-001', 'name' => 'سيراميك أرضيات 60x60', 'unit' => 'متر مربع', 'unit_price' => 180, 'category_id' => 2],
            ['code' => 'CRM-002', 'name' => 'بورسلين حوائط', 'unit' => 'متر مربع', 'unit_price' => 250, 'category_id' => 2],
            ['code' => 'ELC-001', 'name' => 'سلك كهرباء 2.5 مم', 'unit' => 'متر', 'unit_price' => 15, 'category_id' => 4],
            ['code' => 'WOD-001', 'name' => 'خشب MDF', 'unit' => 'لوح', 'unit_price' => 850, 'category_id' => 5],
        ];

        foreach ($materials as $mat) {
            Material::create($mat);
        }

        // Quotation Templates
        QuotationTemplate::create([
            'name' => 'تشطيب شقة كامل',
            'project_type' => 'apartment',
            'description' => 'قالب تشطيب شقة سكنية كاملة',
            'default_items' => [
                ['category' => 'محارة', 'name' => 'محارة حوائط وأسقف', 'unit' => 'متر مربع'],
                ['category' => 'كهرباء', 'name' => 'تأسيس كهرباء كامل', 'unit' => 'نقطة'],
                ['category' => 'سباكة', 'name' => 'تأسيس سباكة كامل', 'unit' => 'نقطة'],
                ['category' => 'دهانات', 'name' => 'دهانات داخلية', 'unit' => 'متر مربع'],
                ['category' => 'أرضيات', 'name' => 'تركيب سيراميك', 'unit' => 'متر مربع'],
            ],
            'terms_conditions' => ['مدة الضمان سنة', 'الدفع على 3 دفعات'],
        ]);

        QuotationTemplate::create([
            'name' => 'تشطيب فيلا',
            'project_type' => 'villa',
            'description' => 'قالب تشطيب فيلا كاملة',
            'default_items' => [
                ['category' => 'محارة', 'name' => 'محارة داخلية وخارجية', 'unit' => 'متر مربع'],
                ['category' => 'كهرباء', 'name' => 'تأسيس كهرباء شامل', 'unit' => 'نقطة'],
                ['category' => 'دهانات', 'name' => 'دهانات داخلية وخارجية', 'unit' => 'متر مربع'],
                ['category' => 'حدائق', 'name' => 'تنسيق حدائق', 'unit' => 'متر مربع'],
            ],
        ]);

        // Quality Checklists
        QualityChecklist::create([
            'name' => 'فحص أعمال الدهانات',
            'category' => 'دهانات',
            'items' => [
                'نظافة السطح قبل الدهان',
                'تطبيق البرايمر',
                'عدد طبقات الدهان',
                'تجانس اللون',
                'عدم وجود تشققات',
            ],
        ]);

        QualityChecklist::create([
            'name' => 'فحص أعمال السيراميك',
            'category' => 'أرضيات',
            'items' => [
                'استواء الأرضية',
                'تطابق الفواصل',
                'عدم وجود فراغات',
                'نظافة الفواصل',
                'ميول الحمامات',
            ],
        ]);

        // Settings
        Setting::set('company_name', 'شركة التشطيبات المتميزة', 'general');
        Setting::set('company_phone', '01000000000', 'general');
        Setting::set('company_email', 'info@company.com', 'general');
        Setting::set('company_address', 'القاهرة - مصر', 'general');
        Setting::set('tax_rate', '14', 'finance', 'float');
        Setting::set('currency', 'EGP', 'finance');

        // Run Mock Data Seeder
        $this->call(MockDataSeeder::class);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LandingSetting;
use App\Models\Section;
use App\Models\Service;
use App\Models\Testimonial;

class LandingSeeder extends Seeder
{
    public function run(): void
    {
        // Settings
        $settings = [
            ['key' => 'site_name', 'value' => 'حازم عبدالله', 'type' => 'text'],
            ['key' => 'site_description', 'value' => 'نقدم حلولاً متكاملة لإدارة أعمالك بكفاءة عالية', 'type' => 'text'],
            ['key' => 'contact_email', 'value' => 'info@hazemabdullah.com', 'type' => 'text'],
            ['key' => 'contact_phone', 'value' => '+966 50 123 4567', 'type' => 'text'],
            ['key' => 'contact_address', 'value' => 'الرياض، المملكة العربية السعودية', 'type' => 'text'],
        ];

        foreach ($settings as $setting) {
            LandingSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }

        // Sections
        $sections = [
            [
                'name' => 'hero',
                'title' => 'حازم عبدالله',
                'subtitle' => 'التصميم الداخلي والديكور',
                'content' => 'نقدم حلولاً متكاملة للتصميم الداخلي والديكور بأعلى معايير الجودة',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'about',
                'title' => 'من نحن',
                'subtitle' => 'تعرف علينا',
                'content' => 'نقدم حلولاً متكاملة لإدارة علاقات العملاء والمشاريع والمبيعات. نساعدك على تنظيم أعمالك وزيادة إنتاجيتك.',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'portfolio',
                'title' => 'أعمالنا',
                'subtitle' => 'مشاريع ناجحة',
                'content' => 'نفخر بتقديم حلول متميزة لعملائنا',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'services',
                'title' => 'خدماتنا',
                'subtitle' => 'ما نقدمه',
                'content' => 'مجموعة متكاملة من الخدمات لتلبية احتياجاتك',
                'order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'testimonials',
                'title' => 'آراء العملاء',
                'subtitle' => 'ماذا يقولون عنا',
                'content' => 'نفخر بثقة عملائنا',
                'order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'contact',
                'title' => 'تواصل معنا',
                'subtitle' => 'نحن هنا لمساعدتك',
                'content' => 'لا تتردد في التواصل معنا',
                'order' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($sections as $section) {
            Section::updateOrCreate(['name' => $section['name']], $section);
        }

        // Services
        $services = [
            [
                'title' => 'إدارة العملاء',
                'description' => 'نظام متكامل لإدارة بيانات العملاء والتواصل معهم',
                'icon' => 'users',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'إدارة المشاريع',
                'description' => 'تتبع المشاريع ومراحلها وفريق العمل',
                'icon' => 'briefcase',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'إدارة المبيعات',
                'description' => 'متابعة العملاء المحتملين وعروض الأسعار',
                'icon' => 'chart-line',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'التقارير والإحصائيات',
                'description' => 'تقارير شاملة لمتابعة أداء العمل',
                'icon' => 'chart-bar',
                'order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(['title' => $service['title']], $service);
        }

        // Testimonials
        $testimonials = [
            [
                'client_name' => 'أحمد محمد',
                'client_position' => 'مدير شركة',
                'content' => 'نظام رائع ساعدنا على تنظيم أعمالنا بشكل كبير',
                'rating' => 5,
                'is_active' => true,
            ],
            [
                'client_name' => 'سارة أحمد',
                'client_position' => 'مديرة مبيعات',
                'content' => 'سهولة الاستخدام والتقارير الشاملة جعلت عملنا أسهل',
                'rating' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::updateOrCreate(['client_name' => $testimonial['client_name']], $testimonial);
        }
    }
}

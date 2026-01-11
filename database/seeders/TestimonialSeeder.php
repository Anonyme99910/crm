<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Testimonial;
use Illuminate\Support\Facades\DB;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing testimonials
        DB::table('testimonials')->truncate();

        // Artistic and professional testimonials
        $testimonials = [
            [
                'client_name' => 'أحمد الحربي',
                'client_position' => 'رئيس مجلس الإدارة',
                'client_company' => 'مجموعة الحربي القابضة',
                'content' => 'تجربة استثنائية مع فريق حازم عبدالله. حولوا فيلتنا إلى تحفة فنية تجمع بين الفخامة والراحة. الاهتمام بأدق التفاصيل والالتزام بالمواعيد جعل التعامل معهم متعة حقيقية.',
                'rating' => 5,
                'is_active' => true,
            ],
            [
                'client_name' => 'نورة السلطان',
                'client_position' => 'مديرة التسويق',
                'client_company' => 'شركة الابتكار للتقنية',
                'content' => 'أبدعوا في تصميم مكاتبنا الجديدة. الفريق محترف ومبدع، استمعوا لاحتياجاتنا وقدموا حلولاً تفوق توقعاتنا. بيئة العمل الآن تلهم الإبداع والإنتاجية.',
                'rating' => 5,
                'is_active' => true,
            ],
            [
                'client_name' => 'خالد العمري',
                'client_position' => 'رجل أعمال',
                'client_company' => 'مؤسسة العمري التجارية',
                'content' => 'من أفضل القرارات التي اتخذتها هي التعاون مع حازم عبدالله. شقتي الآن تعكس شخصيتي وذوقي بشكل مثالي. شكراً على الإبداع والاحترافية.',
                'rating' => 5,
                'is_active' => true,
            ],
            [
                'client_name' => 'فاطمة الزهراني',
                'client_position' => 'مالكة مطعم',
                'client_company' => 'مطعم النكهات الشرقية',
                'content' => 'تصميم مطعمنا كان تحدياً كبيراً، لكن الفريق تجاوز كل التوقعات. الزبائن يشيدون بالأجواء الراقية والتصميم الفريد. استثمار ناجح بكل المقاييس.',
                'rating' => 5,
                'is_active' => true,
            ],
            [
                'client_name' => 'محمد الدوسري',
                'client_position' => 'مهندس معماري',
                'client_company' => 'مكتب الدوسري للاستشارات',
                'content' => 'كمهندس معماري، أقدر العمل المتقن. فريق حازم عبدالله يمتلك رؤية فنية متميزة وقدرة على تنفيذ أصعب التصاميم بدقة متناهية. شريك موثوق لمشاريعنا.',
                'rating' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::create($testimonial);
        }

        $this->command->info('Testimonials seeded successfully!');
    }
}

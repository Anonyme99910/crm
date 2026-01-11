<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Testimonial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing testimonials
        DB::table('testimonials')->truncate();

        // Source directory for images
        $sourceDir = base_path('temp_media/testimonials');

        // Artistic and professional testimonials with client images
        $testimonials = [
            [
                'client_name' => 'أحمد الحربي',
                'client_position' => 'رئيس مجلس الإدارة',
                'client_company' => 'مجموعة الحربي القابضة',
                'content' => 'تجربة استثنائية مع فريق حازم عبدالله. حولوا فيلتنا إلى تحفة فنية تجمع بين الفخامة والراحة. الاهتمام بأدق التفاصيل والالتزام بالمواعيد جعل التعامل معهم متعة حقيقية.',
                'rating' => 5,
                'is_active' => true,
                'image' => 'client1.jpg',
                'video' => 'video1.mp4',
            ],
            [
                'client_name' => 'نورة السلطان',
                'client_position' => 'مديرة التسويق',
                'client_company' => 'شركة الابتكار للتقنية',
                'content' => 'أبدعوا في تصميم مكاتبنا الجديدة. الفريق محترف ومبدع، استمعوا لاحتياجاتنا وقدموا حلولاً تفوق توقعاتنا. بيئة العمل الآن تلهم الإبداع والإنتاجية.',
                'rating' => 5,
                'is_active' => true,
                'image' => 'client4.jpg',
            ],
            [
                'client_name' => 'خالد العمري',
                'client_position' => 'رجل أعمال',
                'client_company' => 'مؤسسة العمري التجارية',
                'content' => 'من أفضل القرارات التي اتخذتها هي التعاون مع حازم عبدالله. شقتي الآن تعكس شخصيتي وذوقي بشكل مثالي. شكراً على الإبداع والاحترافية.',
                'rating' => 5,
                'is_active' => true,
                'image' => 'client2.jpg',
            ],
            [
                'client_name' => 'فاطمة الزهراني',
                'client_position' => 'مالكة مطعم',
                'client_company' => 'مطعم النكهات الشرقية',
                'content' => 'تصميم مطعمنا كان تحدياً كبيراً، لكن الفريق تجاوز كل التوقعات. الزبائن يشيدون بالأجواء الراقية والتصميم الفريد. استثمار ناجح بكل المقاييس.',
                'rating' => 5,
                'is_active' => true,
                'image' => 'client4.jpg',
            ],
            [
                'client_name' => 'محمد الدوسري',
                'client_position' => 'مهندس معماري',
                'client_company' => 'مكتب الدوسري للاستشارات',
                'content' => 'كمهندس معماري، أقدر العمل المتقن. فريق حازم عبدالله يمتلك رؤية فنية متميزة وقدرة على تنفيذ أصعب التصاميم بدقة متناهية. شريك موثوق لمشاريعنا.',
                'rating' => 5,
                'is_active' => true,
                'image' => 'client5.jpg',
            ],
        ];

        // Ensure testimonials directory exists
        Storage::disk('public')->makeDirectory('testimonials');

        foreach ($testimonials as $index => $data) {
            $imageName = $data['image'] ?? null;
            $videoName = $data['video'] ?? null;
            unset($data['image'], $data['video']);

            // Handle client image
            if ($imageName) {
                $sourcePath = $sourceDir . '/' . $imageName;
                if (File::exists($sourcePath)) {
                    $storagePath = 'testimonials/' . ($index + 1) . '_' . $imageName;
                    Storage::disk('public')->put($storagePath, File::get($sourcePath));
                    $data['client_image'] = $storagePath;
                }
            }

            // Handle client video
            if ($videoName) {
                $sourcePath = $sourceDir . '/' . $videoName;
                if (File::exists($sourcePath)) {
                    $storagePath = 'testimonials/' . ($index + 1) . '_' . $videoName;
                    Storage::disk('public')->put($storagePath, File::get($sourcePath));
                    $data['client_video'] = $storagePath;
                }
            }

            Testimonial::create($data);
        }

        $this->command->info('Testimonials seeded successfully with images and videos!');
    }
}

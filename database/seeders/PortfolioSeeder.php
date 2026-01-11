<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PortfolioItem;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class PortfolioSeeder extends Seeder
{
    public function run(): void
    {
        // Artistic portfolio items for interior design
        $portfolioItems = [
            [
                'title' => 'فيلا الواحة الفاخرة',
                'description' => 'تصميم داخلي فاخر لفيلا سكنية بأسلوب عصري يجمع بين الأناقة والراحة',
                'category' => 'تصميم داخلي',
                'client' => 'عائلة الحربي',
                'project_date' => '2024-06-15',
                'location' => 'الرياض، حي النخيل',
                'details' => 'مشروع متكامل يشمل تصميم غرف المعيشة والنوم والمطبخ مع اختيار الأثاث والإضاءة',
                'order' => 1,
                'is_featured' => true,
                'is_active' => true,
                'image' => 'interior1.jpg',
            ],
            [
                'title' => 'مكتب الإبداع التنفيذي',
                'description' => 'تصميم مكتب تنفيذي عصري يعكس الهوية المؤسسية مع لمسات فنية راقية',
                'category' => 'مكاتب',
                'client' => 'شركة الابتكار للتقنية',
                'project_date' => '2024-08-20',
                'location' => 'جدة، حي الشاطئ',
                'details' => 'تصميم مساحة عمل مفتوحة مع مكاتب خاصة وقاعات اجتماعات بتقنيات حديثة',
                'order' => 2,
                'is_featured' => true,
                'is_active' => true,
                'image' => 'interior2.jpg',
            ],
            [
                'title' => 'شقة السماء الزرقاء',
                'description' => 'تصميم شقة سكنية بإطلالة بحرية مستوحاة من ألوان البحر والسماء',
                'category' => 'شقق سكنية',
                'client' => 'السيد خالد العمري',
                'project_date' => '2024-04-10',
                'location' => 'الدمام، كورنيش الخليج',
                'details' => 'استخدام درجات اللون الأزرق والأبيض مع مواد طبيعية لخلق أجواء هادئة',
                'order' => 3,
                'is_featured' => false,
                'is_active' => true,
                'image' => 'interior3.jpg',
            ],
            [
                'title' => 'مطعم النكهات الشرقية',
                'description' => 'تصميم مطعم فاخر يجمع بين الطراز الشرقي الأصيل واللمسات المعاصرة',
                'category' => 'مطاعم',
                'client' => 'مجموعة الضيافة الذهبية',
                'project_date' => '2024-09-05',
                'location' => 'الرياض، طريق الملك فهد',
                'details' => 'تصميم يعكس الثقافة العربية مع إضاءة دافئة وديكورات تراثية معاصرة',
                'order' => 4,
                'is_featured' => true,
                'is_active' => true,
                'image' => 'interior4.jpg',
            ],
            [
                'title' => 'قصر الأحلام',
                'description' => 'تصميم قصر فاخر بطراز كلاسيكي فخم مع لمسات ذهبية أنيقة',
                'category' => 'قصور',
                'client' => 'عائلة السلطان',
                'project_date' => '2024-02-28',
                'location' => 'الرياض، حي الملقا',
                'details' => 'مشروع ضخم يشمل صالات استقبال ومجالس وغرف نوم رئيسية بتشطيبات فاخرة',
                'order' => 5,
                'is_featured' => true,
                'is_active' => true,
                'image' => 'interior5.jpg',
            ],
        ];

        // Source directory for images
        $sourceDir = base_path('temp_media/portfolio');
        
        foreach ($portfolioItems as $item) {
            $imageName = $item['image'];
            unset($item['image']);
            
            // Create or update portfolio item
            $portfolio = PortfolioItem::updateOrCreate(
                ['title' => $item['title']],
                $item
            );

            // Copy image to storage if exists
            $sourcePath = $sourceDir . '/' . $imageName;
            if (File::exists($sourcePath)) {
                $storagePath = 'portfolio/' . $portfolio->id . '_' . $imageName;
                
                // Ensure directory exists
                Storage::disk('public')->makeDirectory('portfolio');
                
                // Copy file to storage
                Storage::disk('public')->put($storagePath, File::get($sourcePath));
                
                // Create media record if not exists
                Media::updateOrCreate(
                    [
                        'mediable_id' => $portfolio->id,
                        'mediable_type' => PortfolioItem::class,
                        'path' => $storagePath,
                    ],
                    [
                        'type' => 'image',
                        'title' => $item['title'],
                        'order' => 1,
                    ]
                );
            }
        }

        $this->command->info('Portfolio items seeded successfully with images!');
    }
}

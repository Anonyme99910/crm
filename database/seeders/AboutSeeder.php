<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Section;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class AboutSeeder extends Seeder
{
    public function run(): void
    {
        // Source directory for images
        $sourceDir = base_path('temp_media/about');

        // Ensure about directory exists in storage
        Storage::disk('public')->makeDirectory('about');

        // Copy about image to storage
        $imageName = 'about1.jpg';
        $sourcePath = $sourceDir . '/' . $imageName;
        $storagePath = 'about/about_section.jpg';
        
        if (File::exists($sourcePath)) {
            Storage::disk('public')->put($storagePath, File::get($sourcePath));
        }

        // Update the about section with the image
        Section::where('name', 'about')->update([
            'background_image' => '/crm/storage/' . $storagePath,
        ]);

        $this->command->info('About section image seeded successfully!');
    }
}

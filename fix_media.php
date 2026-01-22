<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Remove video from testimonial
DB::table('testimonials')->where('id', 1)->update(['client_video' => null]);
echo "Testimonial video removed\n";

// Check image sizes
$storagePath = storage_path('app/public');
$dirs = ['portfolio', 'testimonials', 'about'];
foreach ($dirs as $dir) {
    $path = $storagePath . '/' . $dir;
    if (is_dir($path)) {
        $files = glob($path . '/*');
        foreach ($files as $file) {
            $size = filesize($file) / 1024;
            echo basename($file) . ': ' . round($size) . "KB\n";
        }
    }
}

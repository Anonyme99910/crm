<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Update hero section with video
DB::table('sections')
    ->where('name', 'hero')
    ->update(['background_video' => 'sections/hero_video.mp4']);

echo "Hero section updated with video background!\n";

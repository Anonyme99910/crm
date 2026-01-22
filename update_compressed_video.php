<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

DB::table('sections')
    ->where('name', 'hero')
    ->update(['background_video' => 'sections/hero_compressed.mp4']);

echo "Updated to compressed video!\n";

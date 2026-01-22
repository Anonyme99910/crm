<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$section = DB::table('sections')->where('name', 'hero')->first();
echo "Hero Section:\n";
echo "ID: " . $section->id . "\n";
echo "Name: " . $section->name . "\n";
echo "Background Video: " . ($section->background_video ?? 'NULL') . "\n";

// Check if video file exists
$videoPath = storage_path('app/public/' . $section->background_video);
echo "Video Path: " . $videoPath . "\n";
echo "File Exists: " . (file_exists($videoPath) ? 'YES' : 'NO') . "\n";

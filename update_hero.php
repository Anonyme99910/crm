<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Update hero section with video
DB::table('sections')
    ->where('name', 'hero')
    ->update(['background_video' => '/storage/hero.mp4']);

echo "Hero video updated!\n";

// Show current sections
$sections = DB::table('sections')->get(['name', 'background_video', 'background_image']);
foreach ($sections as $s) {
    echo "{$s->name}: video={$s->background_video}, image={$s->background_image}\n";
}

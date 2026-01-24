<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Fix sections
DB::statement("UPDATE sections SET background_image = REPLACE(background_image, '/crm/', '/') WHERE background_image LIKE '%/crm/%'");

// Fix testimonials (correct column names: client_image, client_video)
DB::statement("UPDATE testimonials SET client_image = REPLACE(client_image, '/crm/', '/') WHERE client_image LIKE '%/crm/%'");
DB::statement("UPDATE testimonials SET client_video = REPLACE(client_video, '/crm/', '/') WHERE client_video LIKE '%/crm/%'");

// Fix media table if exists (try different column names)
try {
    DB::statement("UPDATE media SET path = REPLACE(path, '/crm/', '/') WHERE path LIKE '%/crm/%'");
} catch (\Exception $e) {
    // Column might not exist
}

echo "Paths fixed successfully!\n";

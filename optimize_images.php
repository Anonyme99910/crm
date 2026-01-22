<?php
// Optimize images script
$basePath = '/home/gt-academy/htdocs/gt-academy.com/crm/storage/app/public';
$dirs = ['portfolio', 'testimonials', 'about'];

foreach ($dirs as $dir) {
    $path = $basePath . '/' . $dir;
    if (is_dir($path)) {
        $files = glob($path . '/*.jpg');
        foreach ($files as $file) {
            $sizeBefore = filesize($file) / 1024;
            // Resize to max 1200px and quality 75
            exec("convert \"$file\" -resize 1200x1200\> -quality 75 \"$file\"");
            clearstatcache();
            $sizeAfter = filesize($file) / 1024;
            echo basename($file) . ": " . round($sizeBefore) . "KB -> " . round($sizeAfter) . "KB\n";
        }
    }
}

// Also delete portfolio video
$videoFile = $basePath . '/portfolio/1_video.mp4';
if (file_exists($videoFile)) {
    unlink($videoFile);
    echo "Deleted portfolio video\n";
}

echo "Done!\n";

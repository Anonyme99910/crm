<?php

// Create placeholder images for portfolio and testimonials
$portfolioItems = [
    'modern-living-room-1.jpg',
    'modern-living-room-2.jpg',
    'luxury-kitchen-1.jpg',
    'luxury-kitchen-2.jpg',
    'master-bedroom-suite-1.jpg',
    'master-bedroom-suite-2.jpg',
    'home-office-1.jpg',
    'home-office-2.jpg',
    'outdoor-patio-1.jpg',
    'outdoor-patio-2.jpg',
    'kids-room-1.jpg',
    'kids-room-2.jpg'
];

$testimonials = [
    'sarah-johnson.jpg',
    'michael-smith.jpg',
    'emily-chen.jpg',
    'robert-williams.jpg',
    'lisa-anderson.jpg'
];

// Create portfolio placeholder images
foreach ($portfolioItems as $image) {
    $img = imagecreatetruecolor(800, 600);
    $bgColor = imagecolorallocate($img, 184, 130, 74); // Bronze color
    $textColor = imagecolorallocate($img, 255, 255, 255);
    
    imagefill($img, 0, 0, $bgColor);
    
    // Add text
    $title = str_replace('-', ' ', pathinfo($image, PATHINFO_FILENAME));
    imagettftext($img, 20, 0, 50, 300, $textColor, __DIR__ . '/arial.ttf', $title);
    
    imagejpeg($img, __DIR__ . '/public/storage/portfolio/' . $image);
    imagedestroy($img);
}

// Create testimonial placeholder images
foreach ($testimonials as $image) {
    $img = imagecreatetruecolor(200, 200);
    $bgColor = imagecolorallocate($img, 200, 200, 200);
    $textColor = imagecolorallocate($img, 100, 100, 100);
    
    imagefill($img, 0, 0, $bgColor);
    
    // Add circle background
    imagefilledellipse($img, 100, 100, 150, 150, $textColor);
    
    // Add initials
    $name = pathinfo($image, PATHINFO_FILENAME);
    $initials = strtoupper(substr($name, 0, 2));
    imagettftext($img, 40, 0, 70, 120, $bgColor, __DIR__ . '/arial.ttf', $initials);
    
    imagejpeg($img, __DIR__ . '/public/storage/testimonials/' . $image);
    imagedestroy($img);
}

echo "Placeholder images created successfully!\n";

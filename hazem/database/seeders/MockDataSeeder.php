<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PortfolioItem;
use App\Models\Testimonial;
use App\Models\Media;
use App\Models\Service;

class MockDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create portfolio items with media
        $portfolioItems = [
            [
                'title' => 'Modern Living Room',
                'description' => 'A contemporary living space with clean lines and neutral tones',
                'category' => 'Living Room',
                'client' => 'The Johnson Family',
                'project_date' => '2024-01-15',
                'location' => 'New York, NY',
                'details' => 'Complete renovation of a living room featuring custom built-ins, modern furniture, and integrated smart home technology.',
                'order' => 1,
                'is_featured' => true,
            ],
            [
                'title' => 'Luxury Kitchen',
                'description' => 'High-end kitchen with marble countertops and custom cabinetry',
                'category' => 'Kitchen',
                'client' => 'The Smith Family',
                'project_date' => '2024-02-20',
                'location' => 'Los Angeles, CA',
                'details' => 'Full kitchen remodel with premium appliances, custom island, and wine storage.',
                'order' => 2,
                'is_featured' => true,
            ],
            [
                'title' => 'Master Bedroom Suite',
                'description' => 'Serene bedroom retreat with spa-like bathroom',
                'category' => 'Bedroom',
                'client' => 'The Williams Family',
                'project_date' => '2024-03-10',
                'location' => 'Chicago, IL',
                'details' => 'Complete master suite redesign including bedroom, bathroom, and walk-in closet.',
                'order' => 3,
                'is_featured' => false,
            ],
            [
                'title' => 'Home Office',
                'description' => 'Productive workspace with ergonomic design',
                'category' => 'Office',
                'client' => 'Tech Startup',
                'project_date' => '2024-04-05',
                'location' => 'San Francisco, CA',
                'details' => 'Modern home office with custom desk, storage solutions, and video conferencing setup.',
                'order' => 4,
                'is_featured' => false,
            ],
            [
                'title' => 'Outdoor Patio',
                'description' => 'Stunning outdoor living space with fireplace',
                'category' => 'Outdoor',
                'client' => 'The Brown Family',
                'project_date' => '2024-05-12',
                'location' => 'Miami, FL',
                'details' => 'Complete outdoor transformation with seating areas, fire pit, and landscape design.',
                'order' => 5,
                'is_featured' => true,
            ],
            [
                'title' => 'Kids Room',
                'description' => 'Colorful and functional children\'s bedroom',
                'category' => 'Bedroom',
                'client' => 'The Davis Family',
                'project_date' => '2024-06-18',
                'location' => 'Seattle, WA',
                'details' => 'Fun and functional kids room with custom storage, study area, and play space.',
                'order' => 6,
                'is_featured' => false,
            ],
        ];

        foreach ($portfolioItems as $item) {
            $portfolio = PortfolioItem::create($item);
            
            // Add mock media for each portfolio item
            Media::create([
                'mediable_id' => $portfolio->id,
                'mediable_type' => PortfolioItem::class,
                'type' => 'image',
                'path' => 'portfolio/' . strtolower(str_replace(' ', '-', $item['title'])) . '-1.jpg',
                'title' => $item['title'] . ' - View 1',
                'order' => 1,
            ]);
            
            Media::create([
                'mediable_id' => $portfolio->id,
                'mediable_type' => PortfolioItem::class,
                'type' => 'image',
                'path' => 'portfolio/' . strtolower(str_replace(' ', '-', $item['title'])) . '-2.jpg',
                'title' => $item['title'] . ' - View 2',
                'order' => 2,
            ]);
        }

        // Create testimonials with images and videos
        $testimonials = [
            [
                'client_name' => 'Sarah Johnson',
                'client_position' => 'CEO',
                'client_company' => 'Tech Innovations Inc.',
                'client_image' => 'testimonials/sarah-johnson.jpg',
                'client_video' => null,
                'content' => 'Hazem transformed our office space into something truly remarkable. The attention to detail and innovative design solutions exceeded our expectations. Our team is more productive and inspired than ever!',
                'rating' => 5,
                'order' => 1,
            ],
            [
                'client_name' => 'Michael Smith',
                'client_position' => 'Homeowner',
                'client_company' => null,
                'client_image' => 'testimonials/michael-smith.jpg',
                'client_video' => 'testimonials/michael-smimonial.mp4',
                'content' => 'Working with Hazem was an absolute pleasure. He understood our vision perfectly and brought it to life in ways we couldn\'t have imagined. Our home is now a beautiful reflection of our family.',
                'rating' => 5,
                'order' => 2,
            ],
            [
                'client_name' => 'Emily Chen',
                'client_position' => 'Interior Designer',
                'client_company' => 'Design Studio',
                'client_image' => 'testimonials/emily-chen.jpg',
                'client_video' => null,
                'content' => 'As a fellow designer, I have immense respect for Hazem\'s work. His ability to blend functionality with aesthetics is truly exceptional. I highly recommend his services.',
                'rating' => 5,
                'order' => 3,
            ],
            [
                'client_name' => 'Robert Williams',
                'client_position' => 'Restaurant Owner',
                'client_company' => 'The Gourmet Place',
                'client_image' => 'testimonials/robert-williams.jpg',
                'client_video' => null,
                'content' => 'Hazem completely redesigned our restaurant space. The new ambiance has attracted more customers and created a dining experience that keeps them coming back. Worth every investment!',
                'rating' => 5,
                'order' => 4,
            ],
            [
                'client_name' => 'Lisa Anderson',
                'client_position' => 'Real Estate Agent',
                'client_company' => 'Luxury Properties',
                'client_image' => 'testimonials/lisa-anderson.jpg',
                'client_video' => 'testimonials/lisa-testimonial.mp4',
                'content' => 'I\'ve worked with many designers over the years, but Hazem stands out for his creativity and professionalism. His designs consistently increase property values and impress buyers.',
                'rating' => 5,
                'order' => 5,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::create($testimonial);
        }

        // Add more services
        $additionalServices = [
            [
                'title' => 'Lighting Design',
                'description' => 'Custom lighting solutions that enhance ambiance and functionality',
                'icon' => 'lightbulb',
                'order' => 5,
                'is_active' => true,
            ],
            [
                'title' => 'Renovation Management',
                'description' => 'Complete project management from concept to completion',
                'icon' => 'tools',
                'order' => 6,
                'is_active' => true,
            ],
            [
                'title' => 'Sustainable Design',
                'description' => 'Eco-friendly design solutions for a greener home',
                'icon' => 'leaf',
                'order' => 7,
                'is_active' => true,
            ],
        ];

        foreach ($additionalServices as $service) {
            Service::create($service);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Setting;
use App\Models\Section;
use App\Models\Service;
use App\Models\PortfolioItem;
use App\Models\Testimonial;
use App\Models\Media;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@hazem.com',
            'password' => Hash::make('admin123'),
        ]);

        Setting::create(['key' => 'site_title', 'value' => 'Hazem Abdullah', 'type' => 'text']);
        Setting::create(['key' => 'site_subtitle', 'value' => 'HOME DESIGN', 'type' => 'text']);
        Setting::create(['key' => 'site_description', 'value' => 'Creating beautiful and functional living spaces', 'type' => 'text']);
        Setting::create(['key' => 'contact_email', 'value' => 'info@hazemabdullah.com', 'type' => 'text']);
        Setting::create(['key' => 'contact_phone', 'value' => '+1 234 567 8900', 'type' => 'text']);
        Setting::create(['key' => 'contact_address', 'value' => '123 Design Street, City, Country', 'type' => 'text']);
        Setting::create(['key' => 'social_facebook', 'value' => '', 'type' => 'text']);
        Setting::create(['key' => 'social_instagram', 'value' => '', 'type' => 'text']);
        Setting::create(['key' => 'social_twitter', 'value' => '', 'type' => 'text']);
        Setting::create(['key' => 'social_linkedin', 'value' => '', 'type' => 'text']);

        Section::create([
            'name' => 'hero',
            'title' => 'Hazem Abdullah',
            'subtitle' => 'HOME DESIGN',
            'content' => 'Creating beautiful and functional living spaces that inspire',
            'background_color' => '#f9ede0',
            'order' => 1,
            'is_active' => true,
        ]);

        Section::create([
            'name' => 'about',
            'title' => 'About Us',
            'subtitle' => 'Our Story',
            'content' => 'We are passionate about creating exceptional home designs that blend aesthetics with functionality. With years of experience in interior design, we transform spaces into beautiful, livable works of art.',
            'order' => 2,
            'is_active' => true,
        ]);

        Section::create([
            'name' => 'portfolio',
            'title' => 'Our Portfolio',
            'subtitle' => 'Featured Projects',
            'content' => 'Explore our collection of stunning home design projects',
            'order' => 3,
            'is_active' => true,
        ]);

        Section::create([
            'name' => 'services',
            'title' => 'Our Services',
            'subtitle' => 'What We Offer',
            'content' => 'Comprehensive design solutions for your home',
            'order' => 4,
            'is_active' => true,
        ]);

        Section::create([
            'name' => 'testimonials',
            'title' => 'Client Testimonials',
            'subtitle' => 'What Our Clients Say',
            'content' => 'Read what our satisfied clients have to say about our work',
            'order' => 5,
            'is_active' => true,
        ]);

        Section::create([
            'name' => 'contact',
            'title' => 'Get In Touch',
            'subtitle' => 'Contact Us',
            'content' => 'Let\'s discuss your next project',
            'order' => 6,
            'is_active' => true,
        ]);

        Service::create([
            'title' => 'Interior Design',
            'description' => 'Complete interior design services for residential and commercial spaces',
            'icon' => 'home',
            'order' => 1,
            'is_active' => true,
        ]);

        Service::create([
            'title' => 'Space Planning',
            'description' => 'Optimize your space with our expert planning and layout services',
            'icon' => 'layout',
            'order' => 2,
            'is_active' => true,
        ]);

        Service::create([
            'title' => 'Custom Furniture',
            'description' => 'Bespoke furniture design tailored to your unique style and needs',
            'icon' => 'sofa',
            'order' => 3,
            'is_active' => true,
        ]);

        Service::create([
            'title' => 'Color Consultation',
            'description' => 'Professional color schemes that bring your vision to life',
            'icon' => 'palette',
            'order' => 4,
            'is_active' => true,
        ]);

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
                'portfolio_item_id' => $portfolio->id,
                'type' => 'image',
                'path' => 'portfolio/' . strtolower(str_replace(' ', '-', $item['title'])) . '-1.jpg',
                'alt_text' => $item['title'] . ' - View 1',
                'order' => 1,
            ]);
            
            Media::create([
                'portfolio_item_id' => $portfolio->id,
                'type' => 'image',
                'path' => 'portfolio/' . strtolower(str_replace(' ', '-', $item['title'])) . '-2.jpg',
                'alt_text' => $item['title'] . ' - View 2',
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

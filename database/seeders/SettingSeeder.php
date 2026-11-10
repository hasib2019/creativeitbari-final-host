<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // General
            'site_name' => 'CreativeItBari',
            'site_tagline' => 'Software, Web Applications, E-commerce & Hosting',
            'currency_symbol' => '৳',
            'currency_code' => 'BDT',

            // Contact
            'phone' => '01738356180',
            'email' => 'admin@creativeitbari.com',
            'whatsapp' => '8801738356180',
            'address' => '217/1, 3rd Colony, Lalkuthi, Mirpur-1, Dhaka-1216',
            'working_hours' => 'Sat – Thu, 10:00 AM – 8:00 PM',

            // Hero
            'hero_badge' => 'Software • Web Apps • Hosting • Domains',
            'hero_title' => 'We build software that',
            'hero_highlight' => 'grows your business',
            'hero_subtitle' => 'Ready-made SaaS & one-time software, custom web applications and e-commerce stores — launched on our fast, secure hosting with your own domain.',
            'hero_primary_text' => 'Explore Products',
            'hero_primary_url' => '/products',
            'hero_secondary_text' => 'Get a Free Quote',
            'hero_secondary_url' => '/get-a-quote',

            'home_stats' => [
                ['value' => '150+', 'label' => 'Projects Delivered'],
                ['value' => '50+', 'label' => 'Software Products'],
                ['value' => '99.9%', 'label' => 'Hosting Uptime'],
                ['value' => '24/7', 'label' => 'Customer Support'],
            ],
            'home_process' => [
                ['title' => 'Discovery', 'description' => 'We learn your business, goals and users, then recommend the right product or a custom build.'],
                ['title' => 'Design & Plan', 'description' => 'Clear scope, timeline and UI designs you approve before a single line of code is written.'],
                ['title' => 'Build & Launch', 'description' => 'Agile development with weekly demos, then launch on our optimized hosting with your domain.'],
                ['title' => 'Support & Grow', 'description' => 'Updates, backups, security and new features — we stay with you as your business grows.'],
            ],
            'home_why_us' => [
                ['icon' => 'heroicon-o-bolt', 'title' => 'Fast Delivery', 'description' => 'Ready-made products go live in 24–48 hours; custom projects ship in weekly milestones.'],
                ['icon' => 'heroicon-o-shield-check', 'title' => 'Secure by Default', 'description' => 'SSL, daily backups, hardened servers and secure coding practices on every project.'],
                ['icon' => 'heroicon-o-lifebuoy', 'title' => 'Real Local Support', 'description' => 'Talk to the engineers who built your product — by phone, WhatsApp or email.'],
                ['icon' => 'heroicon-o-banknotes', 'title' => 'Fair, Clear Pricing', 'description' => 'Transparent one-time or subscription pricing in BDT. No hidden fees, ever.'],
            ],

            'cta_title' => 'Ready to build something great?',
            'cta_subtitle' => 'Tell us about your idea — we will reply within 24 hours with a plan and a quote.',
            'cta_button_text' => 'Start Your Project',
            'cta_button_url' => '/get-a-quote',

            // About
            'about_title' => 'We turn ideas into reliable software',
            'about_content' => '<p>CreativeItBari is a Dhaka-based software company helping businesses go digital. We build and sell <strong>SaaS products</strong>, <strong>one-time license software</strong>, <strong>e-commerce solutions</strong> and <strong>custom web applications</strong> — and we host them on our own fast, secure servers with domains registered in your name.</p><p>From startups launching their first online store to established companies automating operations, our team of developers, designers and support engineers delivers products that are simple to use and built to last.</p>',
            'about_mission' => 'Make world-class software affordable and accessible for every business in Bangladesh and beyond.',
            'about_vision' => 'To be the most trusted one-stop technology partner — from domain and hosting to the software that runs your business.',
            'about_experience_years' => '8',

            // Footer
            'footer_about' => 'We build SaaS products, custom software, e-commerce stores and web applications — and keep them online with fast, secure hosting and domains.',

            // SEO
            'seo_meta_title' => 'CreativeItBari — Software Company in Bangladesh | SaaS, Web Apps, E-commerce & Hosting',
            'seo_meta_description' => 'CreativeItBari builds SaaS & one-time software, custom web applications and e-commerce websites in Bangladesh, with fast web hosting and domain registration.',
            'seo_meta_keywords' => 'software company in Bangladesh, SaaS, web application development, ecommerce website, web hosting BD, domain registration Bangladesh, POS software, school management software',
        ];

        // Only fill keys that don't exist yet so re-seeding never overwrites admin edits.
        $existing = Setting::pluck('key')->all();
        Setting::setMany(array_diff_key($defaults, array_flip($existing)));
    }
}

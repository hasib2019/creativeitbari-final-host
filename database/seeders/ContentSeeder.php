<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\Client;
use App\Models\Faq;
use App\Models\Page;
use App\Models\Portfolio;
use App\Models\Post;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        if (Service::exists()) {
            return; // demo data only on a fresh install
        }

        $services = [
            ['Custom Software Development', 'heroicon-o-code-bracket', 'Tailor-made business software — ERP, CRM, inventory and automation built around your exact workflow.', ['Requirement analysis', 'Scalable architecture', 'API & third-party integration', 'Source code ownership']],
            ['Web Application Development', 'heroicon-o-window', 'Fast, secure and SEO-friendly web apps with Laravel, React and Vue — from MVP to enterprise scale.', ['Laravel & React experts', 'Responsive UI', 'Payment gateway integration', 'Performance optimization']],
            ['E-commerce Development', 'heroicon-o-shopping-cart', 'Online stores and marketplaces with bKash, Nagad, SSLCommerz, courier integration and powerful admin.', ['Single & multi-vendor', 'Local payment gateways', 'Courier API integration', 'Facebook & Google tracking']],
            ['Mobile App Development', 'heroicon-o-device-phone-mobile', 'Cross-platform Android & iOS apps with Flutter that connect seamlessly to your web platform.', ['Flutter cross-platform', 'Push notifications', 'Play Store & App Store publishing', 'Offline support']],
            ['UI/UX Design', 'heroicon-o-paint-brush', 'Clean, modern interfaces and brand identities designed to convert visitors into customers.', ['Wireframes & prototypes', 'Design systems', 'Brand identity', 'Usability testing']],
            ['SEO & Digital Marketing', 'heroicon-o-presentation-chart-line', 'Rank higher on Google and grow sales with technical SEO, content and performance marketing.', ['Technical SEO audit', 'Keyword research & content', 'Google & Facebook Ads', 'Monthly reports']],
        ];
        foreach ($services as $i => [$title, $icon, $short, $features]) {
            Service::create([
                'title' => $title, 'slug' => Str::slug($title), 'icon' => $icon,
                'short_description' => $short, 'features' => $features,
                'content' => "<h2>{$title} that drives results</h2><p>{$short}</p><p>Our process is simple: we understand your goals, propose the right solution, design it with you, build it in short iterations and support it long after launch. You get a dedicated project manager, weekly progress demos and full transparency on timeline and cost.</p><h3>Why work with us</h3><ul><li>Experienced in-house team — no outsourcing</li><li>Clear fixed-price or monthly engagement</li><li>Free hosting setup and domain configuration</li><li>Post-launch support and maintenance</li></ul>",
                'is_featured' => $i < 3, 'sort_order' => $i,
            ]);
        }

        $portfolios = [
            ['FreshMart Online Grocery', 'E-commerce', 'FreshMart BD', ['Laravel', 'Vue.js', 'bKash API']],
            ['EduCare School Portal', 'Web Application', 'EduCare Academy', ['Laravel', 'Flutter', 'MySQL']],
            ['MedLife Clinic System', 'Software', 'MedLife Diagnostic', ['Laravel', 'Livewire']],
            ['TripNest Travel Booking', 'Web Application', 'TripNest Tours', ['Laravel', 'React']],
            ['StyleHub Fashion Store', 'E-commerce', 'StyleHub', ['Laravel', 'Tailwind CSS']],
            ['BuildPro Corporate Website', 'Website', 'BuildPro Engineering', ['Laravel', 'Alpine.js']],
        ];
        foreach ($portfolios as $i => [$title, $category, $client, $tech]) {
            Portfolio::create([
                'title' => $title, 'slug' => Str::slug($title), 'category' => $category, 'client_name' => $client,
                'technologies' => $tech, 'completed_at' => now()->subMonths($i * 2 + 1),
                'short_description' => "A {$category} project delivered for {$client}, focused on speed, usability and measurable business growth.",
                'content' => "<h2>The challenge</h2><p>{$client} needed a modern, reliable platform to replace manual processes and reach more customers online.</p><h2>Our solution</h2><p>We designed and built a custom {$category} solution with an intuitive admin panel, mobile-first design and integrations with local payment and SMS gateways.</p><h2>The result</h2><p>Faster operations, happier customers and a platform ready to scale.</p>",
                'is_featured' => $i < 3, 'sort_order' => $i,
            ]);
        }

        $testimonials = [
            ['Rafiq Ahmed', 'Owner', 'FreshMart BD', 'Our online grocery went live in three weeks. Orders, delivery and bKash payments all work flawlessly — sales doubled in the first quarter.'],
            ['Nusrat Jahan', 'Principal', 'EduCare Academy', 'The school management system saved our office hours of manual work every day. Parents love getting attendance and results by SMS.'],
            ['Tanvir Hasan', 'Managing Director', 'TripNest Tours', 'Professional team that actually listens. The booking platform is fast and our agents use it without any training.'],
            ['Sadia Islam', 'Founder', 'StyleHub', 'Beautiful store design, quick support on WhatsApp and the hosting has never gone down. Highly recommended.'],
            ['Kamrul Hossain', 'CEO', 'BuildPro Engineering', 'They handled everything — domain, hosting, website and SEO. We now get regular leads from Google.'],
            ['Dr. Farhana Akter', 'Director', 'MedLife Diagnostic', 'Patient registration and report delivery are now completely digital. Great software at a fair price.'],
        ];
        foreach ($testimonials as $i => [$name, $designation, $company, $content]) {
            Testimonial::create(compact('name', 'designation', 'company', 'content') + ['rating' => 5, 'sort_order' => $i]);
        }

        $faqs = [
            ['General', 'How long does it take to launch a ready-made software?', 'Most of our ready-made products (POS, school management, e-commerce) are installed, configured with your branding and live within 24–48 hours after payment.'],
            ['General', 'Can you customize a product for my business?', 'Yes. Every product can be customized — new modules, reports, integrations or design changes. We give a fixed quote before starting.'],
            ['General', 'What is the difference between SaaS and one-time software?', 'SaaS runs on our cloud for a monthly/yearly fee that includes hosting, updates and support. One-time software is a lifetime license you pay once and can install on your own server.'],
            ['General', 'Which payment methods do you accept?', 'bKash, Nagad, Rocket, bank transfer and cards. For businesses we also provide invoices with VAT where applicable.'],
            ['General', 'Do you provide support after delivery?', 'Absolutely. All products include free support for the period mentioned in the plan, and we offer affordable maintenance packages after that.'],
            ['Hosting', 'Is SSL free with your hosting?', 'Yes, every hosting plan includes free auto-renewing SSL certificates for all your domains.'],
            ['Hosting', 'Can you migrate my existing website for free?', 'Yes, we migrate cPanel and WordPress websites for free with zero downtime in most cases.'],
            ['Hosting', 'Where are your servers located?', 'We use high-performance data centers in Singapore and the USA with NVMe storage and LiteSpeed web servers for low latency in Bangladesh.'],
        ];
        foreach ($faqs as $i => [$category, $question, $answer]) {
            Faq::create(compact('category', 'question', 'answer') + ['sort_order' => $i]);
        }

        foreach ([['Founder Name', 'Founder & CEO'], ['Developer Name', 'Lead Developer'], ['Designer Name', 'Head of Design'], ['Support Name', 'Customer Success']] as $i => [$name, $designation]) {
            TeamMember::create(['name' => $name, 'designation' => $designation, 'sort_order' => $i, 'social_links' => []]);
        }

        foreach (['FreshMart', 'EduCare', 'MedLife', 'TripNest', 'StyleHub', 'BuildPro'] as $i => $name) {
            Client::create(['name' => $name, 'sort_order' => $i]);
        }

        $pages = [
            ['Privacy Policy', '<p>We respect your privacy. This policy explains what information we collect when you use our website and services, and how we use and protect it.</p><h2>Information we collect</h2><p>Name, email, phone number and any details you submit through our forms, plus standard analytics data such as pages visited.</p><h2>How we use it</h2><p>To respond to your inquiries, deliver products and services, send invoices and important updates. We never sell your data.</p><h2>Contact</h2><p>For any privacy question, contact us using the details on our Contact page.</p>'],
            ['Terms & Conditions', '<p>By using our website, products or services you agree to these terms.</p><h2>Services</h2><p>Scope, timeline and price for each project or product are defined in the quotation or plan you accept.</p><h2>Payments</h2><p>Subscriptions renew automatically unless cancelled. Hosting and domains must be renewed before expiry to avoid suspension.</p><h2>Intellectual property</h2><p>Licenses are granted as described in each product plan. Custom project source code ownership transfers upon full payment unless agreed otherwise.</p>'],
            ['Refund Policy', '<p>We want you to be happy with your purchase.</p><h2>Software</h2><p>One-time licenses are refundable within 7 days if the product does not work as described and we are unable to fix it.</p><h2>Hosting</h2><p>Hosting plans include a 15-day money-back guarantee. Domain registrations are non-refundable.</p>'],
        ];
        foreach ($pages as [$title, $content]) {
            Page::create(['title' => $title, 'slug' => Str::slug($title), 'content' => $content]);
        }

        $author = User::where('is_admin', true)->first();
        $blogCategories = collect(['Web Development', 'E-commerce', 'Hosting & Domain', 'Business Software'])
            ->mapWithKeys(fn ($name, $i) => [$name => BlogCategory::create(['name' => $name, 'slug' => Str::slug($name), 'sort_order' => $i])]);

        $posts = [
            ['How to Start an Online Business in Bangladesh in 2026', 'E-commerce', 'A step-by-step guide to choosing a domain, hosting, e-commerce platform, payment gateway and courier partner for your new online store.'],
            ['SaaS vs One-time Software: Which Is Right for Your Business?', 'Business Software', 'Compare total cost, maintenance, updates and control to decide between a subscription and a lifetime license.'],
            ['Choosing the Right Web Hosting: Shared, WordPress or VPS?', 'Hosting & Domain', 'Understand the differences in performance, price and scalability so you pick hosting that fits your traffic and budget.'],
            ['Why Laravel Is Our Framework of Choice for Web Applications', 'Web Development', 'Security, speed of development and a huge ecosystem make Laravel ideal for business-critical web apps.'],
        ];
        foreach ($posts as $i => [$title, $category, $excerpt]) {
            Post::create([
                'title' => $title, 'slug' => Str::slug($title), 'excerpt' => $excerpt,
                'blog_category_id' => $blogCategories[$category]->id, 'user_id' => $author?->id,
                'content' => "<p>{$excerpt}</p><h2>Getting started</h2><p>Every successful digital project starts with clear goals. Define who your customers are, what problem you solve and how you will measure success.</p><h2>Key things to consider</h2><ul><li>Budget and timeline</li><li>Scalability as your business grows</li><li>Security and data backups</li><li>Local payment and delivery integrations</li></ul><h2>Conclusion</h2><p>Choosing the right technology partner saves time and money. Talk to our team for a free consultation.</p>",
                'tags' => ['Bangladesh', $category],
                'is_published' => true, 'is_featured' => $i === 0,
                'published_at' => now()->subDays($i * 5 + 1),
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Portfolio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * CreativeITbari's real projects and clients: the portfolio and the "Trusted
 * by" strip. Screenshots ship in database/seeders/assets/portfolios and are
 * copied to the public disk, so a fresh host gets everything from one
 * `php artisan db:seed`.
 *
 * Safe to run again: a project or client that already exists is left as the
 * admin edited it, and only missing screenshots are copied.
 */
class ProjectSeeder extends Seeder
{
    /** The placeholder work ContentSeeder used to create before real projects existed. */
    private const DEMO_PORTFOLIO_SLUGS = [
        'freshmart-online-grocery', 'educare-school-portal', 'medlife-clinic-system',
        'tripnest-travel-booking', 'stylehub-fashion-store', 'buildpro-corporate-website',
    ];

    private const DEMO_CLIENTS = ['FreshMart', 'EduCare', 'MedLife', 'TripNest', 'StyleHub', 'BuildPro'];

    public function run(): void
    {
        $this->removeDemoContent();

        foreach ($this->projects() as $i => $project) {
            $thumbnail = $this->publish($project['image'] ?? null);
            $gallery = array_values(array_filter([$this->publish($project['gallery'] ?? null)]));

            Portfolio::firstOrCreate(['slug' => $project['slug']], [
                'title' => $project['title'],
                'category' => $project['category'],
                'client_name' => $project['client'],
                'project_url' => $project['url'],
                'thumbnail' => $thumbnail,
                'gallery' => $gallery ?: null,
                'technologies' => $project['tech'],
                'short_description' => $project['summary'],
                'content' => $project['content'],
                'meta_title' => $project['title'].' — '.$project['category'].' by CreativeITbari',
                'meta_description' => $project['summary'],
                'is_featured' => $project['featured'] ?? false,
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        foreach ($this->clients() as $i => [$name, $url]) {
            Client::firstOrCreate(['name' => $name], ['url' => $url, 'sort_order' => $i, 'is_active' => true]);
        }

        $this->command?->info('  Projects: '.count($this->projects()).' portfolio items, '.count($this->clients()).' clients.');
    }

    /**
     * Drop the demo rows only while they are still exactly as seeded, so an
     * install that already reused one for something real keeps it.
     */
    private function removeDemoContent(): void
    {
        Portfolio::whereIn('slug', self::DEMO_PORTFOLIO_SLUGS)->whereNull('thumbnail')->whereNull('project_url')->delete();
        Client::whereIn('name', self::DEMO_CLIENTS)->whereNull('logo')->whereNull('url')->delete();
    }

    /** Copy a bundled screenshot to the public disk once; returns its storage path. */
    private function publish(?string $file): ?string
    {
        if (! $file) {
            return null;
        }

        $source = database_path('seeders/assets/portfolios/'.$file);
        $target = 'portfolios/'.$file;

        if (! File::exists($source)) {
            return null;
        }

        if (! Storage::disk('public')->exists($target)) {
            Storage::disk('public')->put($target, File::get($source));
        }

        return $target;
    }

    /** @return list<array{0: string, 1: ?string}> */
    private function clients(): array
    {
        return [
            ['Metrobox Bangladesh', 'https://metroboxbd.com/'],
            ['CosmeClassy', 'https://cosmeclassy.com/'],
            ['Zara Build', 'https://zarabuild.com/'],
            ['Zaman Technology', 'https://zamanbd.com/'],
            ['Ehavene', 'https://ehavene.com.bd/'],
            ['Apon Health', 'https://www.aponhealth.com/'],
            ['Piara Bazar', 'http://piarabazar.com.bd/'],
            ['MB Imports', 'https://mbimports.com.bd/'],
            ['eSky ERP', 'https://eskyerp.com'],
            ['E-Tali Khata', 'https://etalikhata.com'],
        ];
    }

    /**
     * What each project is was read from the live site itself (its title,
     * description and pages) on 2026-11-11, and the stack from what the site
     * serves (WordPress/WooCommerce or Laravel), not guessed.
     */
    private function projects(): array
    {
        $wpShop = ['WordPress', 'WooCommerce', 'PHP', 'JavaScript', 'HTML5', 'CSS3'];
        $laravelShop = ['Laravel', 'PHP', 'MySQL', 'Bootstrap 4', 'JavaScript', 'jQuery'];

        return [
            [
                'slug' => 'smart-order-solution-erp',
                'title' => 'Smart Order Solution (eSky ERP)',
                'category' => 'ERP Solution',
                'client' => 'eSky ERP',
                'url' => 'https://eskyerp.com',
                'image' => 'eskyerp.webp',
                'tech' => ['Laravel', 'React', 'Inertia.js', 'Tailwind CSS', 'MySQL'],
                'summary' => 'Multi-company ERP for optical retail: POS, lens stock by barcode, purchasing, due collection and accounting — every order tracked in real time with role-based access.',
                'content' => '<p>Smart Order Solution is a cloud ERP where each shop runs as its own company with its own branches, users and roles. It follows an order from the counter to the books.</p>'
                    .'<h2>What it covers</h2><ul><li>Point of sale with lens search, catalog items and price lists</li><li>Lens stock held as specs (power, cyl, axis, addition), barcodes and labels, stock per branch and transfers</li><li>Reorder sheet, purchase orders, goods receipts and supplier payments</li><li>Due collection with approval, cash and bank accounts, expenses</li><li>Double-entry journal, ledger, trial balance, P&amp;L and balance sheet</li></ul>',
                'featured' => true,
            ],
            [
                'slug' => 'creativeitbari-pos-optical-erp',
                'title' => 'CreativeITbari POS — Optical ERP',
                'category' => 'ERP Solution',
                'client' => 'CreativeITbari',
                'url' => 'https://erp.etalikhata.com',
                'image' => 'erp-etalikhata.webp',
                'tech' => ['Laravel', 'PHP', 'MySQL', 'jQuery', 'Bootstrap'],
                'summary' => 'Every order, every branch — one clear view. From the prescription written at the counter to the invoice that settles it, orders, lenses, stock and payments stay in step across branches and the lab.',
                'content' => '<p>An ERP built for opticians, with orders, POS, stock, vouchers and dues in one system.</p>'
                    .'<h2>Highlights</h2><ul><li>Order slips built for opticians: both eyes, distance and near, with lens and edge details recorded exactly as written</li><li>Stock held against the branch the moment an order is taken, released automatically when the voucher posts</li><li>Several orders billed on one invoice, collected like any other sale</li></ul>',
                'featured' => true,
            ],
            [
                'slug' => 'e-tali-khata-app',
                'title' => 'E-Tali Khata — Bangla Accounts App',
                'category' => 'Mobile App',
                'client' => 'E-Tali Khata',
                'url' => 'https://etalikhata.com',
                'image' => 'etalikhata.webp',
                'gallery' => 'etalikhata-full.webp',
                'tech' => ['Mobile App', 'Laravel', 'Bangla & English'],
                'summary' => 'A simple Bangla app that keeps every account in one place: business, personal income and expenses, tuition, rent, samity and mess.',
                'content' => '<p>ই-টালি-খাতা — সব হিসাব, এক অ্যাপে. Shops and families keep their books on the phone instead of in a paper khata.</p>'
                    .'<h2>Features</h2><ul><li>Dues and receivables, cash book, stock, bills and reports</li><li>Separate books for business, personal spending, tuition, rent, samity and mess</li><li>Bangla and English interface</li><li>Secure backup, with a free trial to start</li></ul>',
                'featured' => true,
            ],
            [
                'slug' => 'samity-cooperative-management',
                'title' => 'Samity — Cooperative Management Solution',
                'category' => 'Cooperative Management',
                'client' => 'CreativeITbari',
                'url' => 'https://samity.creativeitbari.com',
                'image' => 'samity.webp',
                'tech' => ['Laravel', 'ReactJS', 'PHP', 'Bootstrap 4', 'JavaScript', 'jQuery'],
                'summary' => 'Run a cooperative (samity) from one place: members, savings, loans and dues — with transparency, security and role-based access for every officer.',
                'content' => '<p>সমিতি ব্যবস্থাপনা: আপনার সমবায় পরিচালনা করুন, একটি জায়গা থেকেই. Built and maintained by CreativeITbari as an individual product.</p>'
                    .'<h2>Modules</h2><ul><li>Member management: members, savings, loans and dues in one place</li><li>Accounts and dues: savings, loans and outstanding amounts kept up to date</li><li>Role-based access: each person sees and does only what their role allows</li><li>Bangla and English</li></ul>',
                'featured' => true,
            ],
            [
                'slug' => 'metrobox-bangladesh',
                'title' => 'Metrobox Bangladesh',
                'category' => 'E-commerce',
                'client' => 'Metrobox Bangladesh',
                'url' => 'https://metroboxbd.com/',
                'image' => 'metroboxbd.webp',
                'gallery' => 'metroboxbd-full.webp',
                'tech' => $wpShop,
                'summary' => 'Online store for skincare, beauty products and supplements, with women’s, men’s and derma care, fragrance and food supplement ranges and delivery across Bangladesh.',
                'content' => '<p>Customers browse by care category, check prices and product details, and order online with delivery inside and outside Dhaka.</p><h2>Built with</h2><p>WordPress and WooCommerce, with a referral program, blog and wish list.</p>',
                'featured' => true,
            ],
            [
                'slug' => 'zara-build',
                'title' => 'Zara Build',
                'category' => 'E-commerce',
                'client' => 'Zara Build',
                'url' => 'https://zarabuild.com/',
                'image' => 'zarabuild.webp',
                'gallery' => 'zarabuild-full.webp',
                'tech' => $laravelShop,
                'summary' => 'E-commerce for sanitary products, home décor and construction materials: building materials, kitchen and bath solutions and ceramics, with flash sales.',
                'content' => '<p>“Building trust, creating comfort.” A Laravel store where customers shop by category or brand, catch flash sales and order fittings for their home.</p>',
                'featured' => true,
            ],
            [
                'slug' => 'cosmeclassy',
                'title' => 'CosmeClassy',
                'category' => 'E-commerce',
                'client' => 'CosmeClassy',
                'url' => 'https://cosmeclassy.com/',
                'image' => 'cosmeclassy.webp',
                'gallery' => 'cosmeclassy-full.webp',
                'tech' => $wpShop,
                'summary' => 'A trusted online shop for authentic cosmetics and skincare in Bangladesh, with brand browsing, pre-orders and product requests.',
                'content' => '<p>Shoppers browse top categories and brands, pre-order items not yet in stock and request products they cannot find.</p>',
            ],
            [
                'slug' => 'zaman-technology',
                'title' => 'Zaman Technology',
                'category' => 'E-commerce',
                'client' => 'Zaman Technology',
                'url' => 'https://zamanbd.com/',
                'image' => 'zamanbd.webp',
                'gallery' => 'zamanbd-full.webp',
                'tech' => $wpShop,
                'summary' => 'Product catalogue and shop for a security and automation distributor: entrance and parking control, CCTV, access control, PA systems and more.',
                'content' => '<p>Turnstiles, boom gates, UHF access and much more, from more than 1,500 products across 17+ brands, with separate paths for resellers and for businesses and institutions.</p>',
            ],
            [
                'slug' => 'ehavene',
                'title' => 'Ehavene',
                'category' => 'E-commerce',
                'client' => 'Ehavene',
                'url' => 'https://ehavene.com.bd/',
                'image' => 'ehavene.webp',
                'gallery' => 'ehavene-full.webp',
                'tech' => $laravelShop,
                'summary' => 'Skin care, health and beauty shop: dermatological care, baby care, men’s and women’s care, pet food, organic and foreign food and medical equipment, with order tracking.',
                'content' => '<p>A Laravel shop with brand pages, a wide category menu and order tracking for customers.</p>',
            ],
            [
                'slug' => 'apon-health',
                'title' => 'Apon Health — Online Pharmacy',
                'category' => 'E-commerce',
                'client' => 'Apon Health',
                'url' => 'https://www.aponhealth.com/',
                'image' => 'aponhealth.webp',
                'gallery' => 'aponhealth-full.webp',
                'tech' => $laravelShop,
                'summary' => 'Online pharmacy in Bangladesh: order medicine by uploading a prescription or by phone, shop health-care products and join as a member.',
                'content' => '<p>Customers upload a prescription or search for a medicine to order it online, or call to order. Health-care products and a free membership sit alongside.</p>',
            ],
            [
                'slug' => 'piara-bazar',
                'title' => 'Piara Bazar',
                'category' => 'E-commerce',
                'client' => 'Piara Bazar',
                'url' => 'http://piarabazar.com.bd/',
                'tech' => ['PHP', 'HTML5', 'CSS3', 'Bootstrap 4', 'JavaScript', 'jQuery'],
                'summary' => 'E-commerce website built for Piara Bazar.',
                'content' => '<p>An online store built for Piara Bazar.</p>',
            ],
            [
                'slug' => 'mb-imports',
                'title' => 'MB Imports',
                'category' => 'E-commerce',
                'client' => 'MB Imports',
                'url' => 'https://mbimports.com.bd/',
                'tech' => ['PHP', 'HTML5', 'CSS3', 'Bootstrap 4', 'JavaScript', 'jQuery'],
                'summary' => 'E-commerce website built for MB Imports.',
                'content' => '<p>An online store built for MB Imports.</p>',
            ],
        ];
    }
}

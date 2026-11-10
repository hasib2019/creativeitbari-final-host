<?php

namespace Database\Seeders;

use App\Models\DomainPrice;
use App\Models\HostingPlan;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        if (Product::exists()) {
            return; // demo data only on a fresh install
        }

        $categories = collect([
            ['name' => 'Business Management', 'icon' => 'heroicon-o-building-office-2'],
            ['name' => 'Education', 'icon' => 'heroicon-o-academic-cap'],
            ['name' => 'Healthcare', 'icon' => 'heroicon-o-heart'],
            ['name' => 'E-commerce', 'icon' => 'heroicon-o-shopping-bag'],
            ['name' => 'Travel & Real Estate', 'icon' => 'heroicon-o-globe-alt'],
        ])->mapWithKeys(fn ($c, $i) => [$c['name'] => ProductCategory::create($c + ['slug' => Str::slug($c['name']), 'sort_order' => $i])]);

        $products = [
            [
                'type' => 'saas', 'category' => 'Business Management', 'is_featured' => true,
                'name' => 'Cloud POS & Inventory',
                'tagline' => 'Sell, track stock and see profits from any device',
                'short_description' => 'Point of sale, inventory, purchase, due tracking and SMS — a cloud POS built for retail shops, pharmacies and super shops.',
                'price' => 1500, 'price_label' => 'From / month',
                'features' => ['Barcode billing & thermal print', 'Multi-branch stock', 'Purchase & supplier dues', 'Customer dues & SMS reminders', 'Profit/loss & VAT reports', 'Works on mobile, tablet & PC'],
                'technologies' => ['Laravel', 'Vue.js', 'MySQL', 'PWA'],
                'plans' => [
                    ['name' => 'Starter', 'price' => 1500, 'period' => 'monthly', 'features' => ['1 branch', '2 users', 'Unlimited products', 'Email support'], 'is_popular' => false],
                    ['name' => 'Business', 'price' => 3000, 'period' => 'monthly', 'features' => ['3 branches', '10 users', 'SMS integration', 'Priority support'], 'is_popular' => true],
                    ['name' => 'Enterprise', 'price' => 30000, 'period' => 'yearly', 'features' => ['Unlimited branches', 'Unlimited users', 'Custom reports', 'Dedicated manager'], 'is_popular' => false],
                ],
            ],
            [
                'type' => 'saas', 'category' => 'Education', 'is_featured' => true,
                'name' => 'School Management System',
                'tagline' => 'Admissions, attendance, exams & fees — all in one place',
                'short_description' => 'Complete cloud school & college management with student/parent apps, online fee collection and result publishing.',
                'price' => 2500, 'price_label' => 'From / month',
                'features' => ['Online admission', 'Attendance with SMS', 'Exam & result card', 'Fee collection (bKash/Nagad)', 'Teacher & payroll', 'Parent mobile app'],
                'technologies' => ['Laravel', 'Flutter', 'MySQL'],
                'plans' => [
                    ['name' => 'Basic', 'price' => 2500, 'period' => 'monthly', 'features' => ['Up to 500 students', 'Core modules', 'Email support'], 'is_popular' => false],
                    ['name' => 'Standard', 'price' => 4500, 'period' => 'monthly', 'features' => ['Up to 2,000 students', 'Parent app', 'SMS gateway'], 'is_popular' => true],
                    ['name' => 'Premium', 'price' => 7500, 'period' => 'monthly', 'features' => ['Unlimited students', 'Custom domain', 'Dedicated support'], 'is_popular' => false],
                ],
            ],
            [
                'type' => 'one_time', 'category' => 'Business Management', 'is_featured' => true,
                'name' => 'HRM & Payroll Software',
                'tagline' => 'Own your HR system — no monthly fees',
                'short_description' => 'Employee records, attendance device integration, leave, payroll and payslips. One-time payment, installed on your server.',
                'price' => 60000, 'sale_price' => 45000, 'price_label' => 'One-time',
                'features' => ['Employee profiles & documents', 'ZKTeco attendance sync', 'Leave & holiday management', 'Salary sheet & payslips', 'Loan & advance', 'Source code available'],
                'technologies' => ['Laravel', 'MySQL', 'Bootstrap'],
                'plans' => [
                    ['name' => 'Regular License', 'price' => 45000, 'period' => 'one_time', 'features' => ['Single company', '1 year free support', 'Free installation'], 'is_popular' => true],
                    ['name' => 'Extended License', 'price' => 90000, 'period' => 'one_time', 'features' => ['Multiple companies', 'Full source code', '2 years support'], 'is_popular' => false],
                ],
            ],
            [
                'type' => 'one_time', 'category' => 'Healthcare',
                'name' => 'Hospital Management System',
                'tagline' => 'From patient registration to billing and pharmacy',
                'short_description' => 'OPD/IPD, doctor appointments, pathology reports, pharmacy and accounts for clinics and hospitals.',
                'price' => 120000, 'price_label' => 'One-time',
                'features' => ['Patient registration & history', 'Doctor appointment & serial', 'Pathology & reports', 'Pharmacy & stock', 'Billing & accounts', 'Role-based access'],
                'technologies' => ['Laravel', 'MySQL', 'Livewire'],
            ],
            [
                'type' => 'ecommerce', 'category' => 'E-commerce', 'is_featured' => true,
                'name' => 'Multi-vendor Marketplace',
                'tagline' => 'Launch your own Daraz-style marketplace',
                'short_description' => 'Vendors, commissions, wallets, courier integration and payment gateways — a complete marketplace ready to scale.',
                'price' => 150000, 'sale_price' => 120000, 'price_label' => 'Starting from',
                'features' => ['Vendor registration & panel', 'Commission & withdrawals', 'SSLCommerz, bKash, Nagad', 'Pathao/Steadfast courier', 'Android & iOS apps', 'SEO optimized storefront'],
                'technologies' => ['Laravel', 'Next.js', 'Flutter', 'Redis'],
            ],
            [
                'type' => 'ecommerce', 'category' => 'E-commerce',
                'name' => 'Single Vendor E-commerce Store',
                'tagline' => 'A fast online shop for your brand',
                'short_description' => 'Beautiful, mobile-first online store with product variations, coupons, Facebook pixel and order management.',
                'price' => 35000, 'price_label' => 'Starting from',
                'features' => ['Unlimited products & variants', 'Cash on delivery & online payment', 'Coupons & flash sales', 'Facebook Pixel & GA4', 'Order & invoice management', 'Free domain + 1 year hosting'],
                'technologies' => ['Laravel', 'Tailwind CSS', 'MySQL'],
            ],
            [
                'type' => 'web_app', 'category' => 'Travel & Real Estate', 'is_featured' => true,
                'name' => 'Travel & Tour Booking App',
                'tagline' => 'Sell tours, hotels and air tickets online',
                'short_description' => 'Tour packages, hotel booking, visa processing and agent panel with online payment for travel agencies.',
                'price' => 80000, 'price_label' => 'Starting from',
                'features' => ['Tour package builder', 'Hotel & room booking', 'Visa application tracking', 'B2B agent panel', 'Online payment', 'Booking invoices & emails'],
                'technologies' => ['Laravel', 'React', 'MySQL'],
            ],
            [
                'type' => 'web_app', 'category' => 'Travel & Real Estate',
                'name' => 'Real Estate Listing Portal',
                'tagline' => 'List, search and sell properties online',
                'short_description' => 'Property listings with map search, agent profiles, lead capture and featured listing packages.',
                'price' => 70000, 'price_label' => 'Starting from',
                'features' => ['Advanced property search', 'Google Maps integration', 'Agent & agency profiles', 'Featured listing packages', 'Lead management', 'Multi-language ready'],
                'technologies' => ['Laravel', 'Alpine.js', 'MySQL'],
            ],
        ];

        foreach ($products as $i => $p) {
            $category = $categories[$p['category']] ?? null;
            unset($p['category']);

            Product::create($p + [
                'slug' => Str::slug($p['name']),
                'product_category_id' => $category?->id,
                'description' => '<h2>Overview</h2><p>'.$p['short_description'].'</p><p>Built by our in-house team, fully customizable to your workflow, and backed by local support. We handle installation, data migration and training so your team can start using it from day one.</p><h3>Who is it for?</h3><p>Growing businesses that want reliable, easy-to-use software without the complexity or cost of foreign enterprise tools.</p>',
                'sort_order' => $i,
            ]);
        }

        $hosting = [
            ['name' => 'Starter', 'type' => 'shared', 'short_description' => 'For personal sites & landing pages', 'monthly_price' => 199, 'yearly_price' => 1500,
                'features' => ['1 Website', '5 GB NVMe SSD', 'Free SSL Certificate', '5 Email Accounts', 'Weekly Backup', 'cPanel Control Panel']],
            ['name' => 'Business', 'type' => 'shared', 'short_description' => 'For businesses & online stores', 'monthly_price' => 399, 'yearly_price' => 3500, 'is_popular' => true,
                'features' => ['5 Websites', '20 GB NVMe SSD', 'Free SSL Certificate', 'Unlimited Email', 'Daily Backup', 'Free .com Domain (1st year)', 'LiteSpeed Cache']],
            ['name' => 'Pro', 'type' => 'shared', 'short_description' => 'For agencies & high-traffic sites', 'monthly_price' => 799, 'yearly_price' => 6500,
                'features' => ['Unlimited Websites', '50 GB NVMe SSD', 'Free SSL Certificate', 'Unlimited Email', 'Daily Backup', 'Free .com Domain (1st year)', 'Priority Support']],
            ['name' => 'WP Starter', 'type' => 'wordpress', 'short_description' => 'Managed WordPress, optimized', 'monthly_price' => 299, 'yearly_price' => 2500,
                'features' => ['1 WordPress Site', '10 GB NVMe SSD', 'One-click Staging', 'Auto Updates', 'Malware Scan', 'Free SSL']],
            ['name' => 'WP Business', 'type' => 'wordpress', 'short_description' => 'WooCommerce ready', 'monthly_price' => 599, 'yearly_price' => 5000, 'is_popular' => true,
                'features' => ['3 WordPress Sites', '30 GB NVMe SSD', 'WooCommerce Optimized', 'Daily Backup', 'Malware Removal', 'Free SSL & CDN']],
            ['name' => 'Reseller Basic', 'type' => 'reseller', 'short_description' => 'Start your own hosting business', 'monthly_price' => 1200, 'yearly_price' => 12000,
                'features' => ['20 cPanel Accounts', '50 GB NVMe SSD', 'WHM Access', 'White-label', 'Free SSL for all', 'WHMCS Discount']],
        ];
        foreach ($hosting as $i => $h) {
            HostingPlan::create($h + ['slug' => Str::slug($h['type'].' '.$h['name']), 'sort_order' => $i]);
        }

        $tlds = [
            ['.com', 1450, 1650, 1450, true], ['.net', 1650, 1850, 1650, true], ['.com.bd', 1500, 1500, null, true],
            ['.org', 1550, 1750, 1550, false], ['.xyz', 350, 1500, 1400, true], ['.info', 650, 2500, 2200, false],
            ['.online', 450, 3800, 3500, false], ['.shop', 350, 3500, 3200, false], ['.store', 450, 5500, 5000, false],
            ['.tech', 650, 5500, 5000, false], ['.io', 5500, 5500, 5500, false], ['.co', 3000, 3200, 3000, false],
        ];
        foreach ($tlds as $i => [$tld, $register, $renew, $transfer, $popular]) {
            DomainPrice::create([
                'tld' => $tld, 'register_price' => $register, 'renew_price' => $renew,
                'transfer_price' => $transfer, 'is_popular' => $popular, 'sort_order' => $i,
            ]);
        }
    }
}

<?php

namespace Tests\Feature;

use App\Filament\Pages\SiteSettings;
use App\Filament\Resources\HostingPlans\Pages\ManageHostingPlans;
use App\Filament\Resources\Inquiries\Pages\EditInquiry;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Models\BlogCategory;
use App\Models\HostingPlan;
use App\Models\Inquiry;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use App\Models\Setting;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public static function indexPages(): array
    {
        return collect([
            'products', 'product-categories', 'hosting-plans', 'domain-prices',
            'services', 'portfolios', 'pages', 'faqs', 'testimonials', 'team-members', 'clients',
            'posts', 'blog-categories',
            'inquiries', 'subscribers',
            'users',
        ])->mapWithKeys(fn (string $slug) => [$slug => [$slug]])->all();
    }

    public static function createPages(): array
    {
        // Simple (modal) resources have no separate create page.
        return collect(['products', 'services', 'portfolios', 'pages', 'posts'])
            ->mapWithKeys(fn (string $slug) => [$slug => [$slug]])
            ->all();
    }

    public function test_dashboard_loads_for_admin(): void
    {
        Inquiry::create(['type' => 'contact', 'name' => 'Jane', 'email' => 'jane@example.com', 'message' => 'Hi']);

        $this->actingAs($this->admin)->get('/admin')->assertOk()->assertSee('New inquiries');
    }

    public function test_non_admin_user_is_forbidden(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    #[DataProvider('indexPages')]
    public function test_resource_index_page_loads(string $slug): void
    {
        $this->actingAs($this->admin)->get("/admin/{$slug}")->assertOk();
    }

    #[DataProvider('createPages')]
    public function test_resource_create_page_loads(string $slug): void
    {
        $this->actingAs($this->admin)->get("/admin/{$slug}/create")->assertOk();
    }

    public function test_inquiries_have_no_create_page(): void
    {
        $this->actingAs($this->admin)->get('/admin/inquiries/create')->assertNotFound();
    }

    public function test_edit_pages_load_with_records(): void
    {
        $category = ProductCategory::create(['name' => 'School', 'slug' => 'school']);
        $product = Product::create([
            'type' => 'saas', 'name' => 'EduSoft', 'slug' => 'edusoft', 'product_category_id' => $category->id,
            'price' => 5000, 'sale_price' => 4000, 'features' => ['Attendance', 'Exams'],
            'plans' => [['name' => 'Basic', 'price' => 1000, 'period' => 'monthly', 'features' => ['A'], 'is_popular' => true]],
            'technologies' => ['Laravel'],
        ]);
        $service = Service::create(['title' => 'Web Development', 'slug' => 'web-development', 'icon' => 'heroicon-o-code-bracket', 'features' => ['Fast']]);
        $blogCategory = BlogCategory::create(['name' => 'News', 'slug' => 'news']);
        $post = Post::create([
            'title' => 'Hello', 'slug' => 'hello', 'content' => '<p>World</p>',
            'blog_category_id' => $blogCategory->id, 'user_id' => $this->admin->id, 'tags' => ['laravel'], 'published_at' => now(),
        ]);

        $this->actingAs($this->admin);

        $this->get("/admin/products/{$product->id}/edit")->assertOk()->assertSee('EduSoft');
        $this->get("/admin/services/{$service->id}/edit")->assertOk()->assertSee('Web Development');
        $this->get("/admin/posts/{$post->id}/edit")->assertOk()->assertSee('Hello');
        $this->get('/admin/products')->assertOk()->assertSee('EduSoft');
        $this->get('/admin/posts')->assertOk()->assertSee('Hello');
    }

    public function test_inquiry_view_and_status_update(): void
    {
        $inquiry = Inquiry::create([
            'type' => 'order', 'name' => 'Karim', 'email' => 'karim@example.com', 'phone' => '01700000000',
            'item_name' => 'EduSoft', 'message' => "Line one\nLine two",
        ]);

        $this->actingAs($this->admin);

        $this->get("/admin/inquiries/{$inquiry->id}")->assertOk()->assertSee('Karim')->assertSee('EduSoft');
        $this->get("/admin/inquiries/{$inquiry->id}/edit")->assertOk();

        Livewire::test(EditInquiry::class, ['record' => $inquiry->getRouteKey()])
            ->fillForm(['status' => 'contacted', 'admin_note' => 'Called him', 'name' => 'Hacker'])
            ->call('save')
            ->assertHasNoFormErrors();

        $inquiry->refresh();
        $this->assertSame('contacted', $inquiry->status);
        $this->assertSame('Called him', $inquiry->admin_note);
        $this->assertSame('Karim', $inquiry->name, 'Read-only fields must not be saved.');
    }

    public function test_product_can_be_created_from_admin(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Shop Pro',
                'slug' => 'shop-pro',
                'type' => 'ecommerce',
                'price' => 15000,
                // Simple repeater items are filled in their internal ['value' => …] form.
                'features' => [['value' => 'Multi vendor'], ['value' => 'bKash payment']],
                'technologies' => ['Laravel', 'Vue'],
                'plans' => [
                    ['name' => 'Starter', 'price' => 999, 'period' => 'monthly', 'features' => ['1 store'], 'is_popular' => false],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::where('slug', 'shop-pro')->firstOrFail();
        $this->assertSame(['Multi vendor', 'bKash payment'], $product->features);
        $this->assertSame('Starter', $product->plans[0]['name']);
    }

    public function test_slug_is_generated_from_name_on_create(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateProduct::class)
            ->fillForm(['name' => 'School Management System'])
            ->assertSchemaStateSet(['slug' => 'school-management-system']);
    }

    public function test_hosting_plan_can_be_created_via_modal(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ManageHostingPlans::class)
            ->callAction(TestAction::make('create'), data: [
                'name' => 'Starter',
                'slug' => 'starter',
                'type' => 'shared',
                'monthly_price' => 150,
                'yearly_price' => 1500,
                'features' => [['value' => '5 GB SSD'], ['value' => 'Free SSL']],
            ])
            ->assertHasNoFormErrors();

        $plan = HostingPlan::where('slug', 'starter')->firstOrFail();
        $this->assertSame(['5 GB SSD', 'Free SSL'], $plan->features);
    }

    public function test_site_settings_page_loads_and_saves(): void
    {
        $this->actingAs($this->admin);

        $this->get('/admin/site-settings')->assertOk()->assertSee('Site Settings');

        Livewire::test(SiteSettings::class)
            ->assertSchemaStateSet(['currency_symbol' => '৳'], 'form')
            ->fillForm([
                'site_name' => 'CreativeItBari Test',
                'phone' => '01738356180',
                'hero_title' => 'We build software',
                'home_stats' => [
                    ['value' => '250+', 'label' => 'Projects'],
                    ['value' => '10+', 'label' => 'Years'],
                ],
                'home_why_us' => [
                    ['icon' => 'heroicon-o-bolt', 'title' => 'Fast', 'description' => 'Quick delivery'],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('CreativeItBari Test', Setting::get('site_name'));
        $this->assertSame('01738356180', Setting::get('phone'));
        $this->assertSame('We build software', Setting::get('hero_title'));
        $this->assertSame('৳', Setting::get('currency_symbol'));
        $this->assertSame([
            ['value' => '250+', 'label' => 'Projects'],
            ['value' => '10+', 'label' => 'Years'],
        ], Setting::get('home_stats'));
        $this->assertSame('heroicon-o-bolt', Setting::get('home_why_us')[0]['icon']);

        // Saved values are loaded back into the form.
        Livewire::test(SiteSettings::class)
            ->assertSchemaStateSet(['site_name' => 'CreativeItBari Test', 'phone' => '01738356180'], 'form');
    }

    public function test_site_settings_upload_is_saved_as_plain_path(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin);

        Livewire::test(SiteSettings::class)
            ->fillForm(['logo' => UploadedFile::fake()->image('logo.png', 200, 50)])
            ->call('save')
            ->assertHasNoFormErrors();

        $logo = Setting::get('logo');
        $this->assertIsString($logo);
        $this->assertStringStartsWith('settings/', $logo);
        Storage::disk('public')->assertExists($logo);

        // Re-opening the page with a stored path still renders.
        $this->get('/admin/site-settings')->assertOk();
    }
}

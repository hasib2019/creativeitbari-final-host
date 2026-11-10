<?php

namespace Tests\Feature;

use App\Mail\NewInquiryMail;
use App\Models\Inquiry;
use App\Models\Post;
use App\Models\Product;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FrontendTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public static function publicPages(): array
    {
        return [
            'home' => ['/'],
            'products' => ['/products'],
            'products by type' => ['/products?type=ecommerce'],
            'product' => ['/products/cloud-pos-inventory'],
            'services' => ['/services'],
            'service' => ['/services/e-commerce-development'],
            'hosting' => ['/hosting'],
            'domains' => ['/domains'],
            'portfolio' => ['/portfolio'],
            'portfolio item' => ['/portfolio/metrobox-bangladesh'],
            'blog' => ['/blog'],
            'blog category' => ['/blog/category/e-commerce'],
            'blog search' => ['/blog?q=hosting'],
            'about' => ['/about'],
            'faq' => ['/faq'],
            'contact' => ['/contact'],
            'quote' => ['/get-a-quote'],
            'page' => ['/page/privacy-policy'],
            'product order' => ['/order?product=cloud-pos-inventory&plan=Business'],
            'hosting order' => ['/order?hosting=shared-business&cycle=monthly'],
            'domain order' => ['/order?domain=example.com'],
        ];
    }

    #[DataProvider('publicPages')]
    public function test_public_pages_render(string $url): void
    {
        $this->get($url)
            ->assertOk()
            ->assertSee('<link rel="canonical"', false)
            ->assertSee('application/ld+json', false);
    }

    public function test_home_has_seo_meta_and_seeded_content(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<meta name="description"', false)
            ->assertSee('og:title', false)
            ->assertSee('Cloud POS &amp; Inventory', false)
            ->assertSee('"@type":"Organization"', false);
    }

    public function test_inactive_and_missing_records_return_404(): void
    {
        Product::where('slug', 'cloud-pos-inventory')->update(['is_active' => false]);
        Post::query()->update(['is_published' => false]);

        $this->get('/products/cloud-pos-inventory')->assertNotFound();
        $this->get('/blog/how-to-start-an-online-business-in-bangladesh-in-2026')->assertNotFound();
        $this->get('/products/does-not-exist')->assertNotFound();
        $this->get('/this-page-does-not-exist')->assertNotFound()->assertSee('Page not found');
    }

    public function test_contact_form_creates_inquiry_and_notifies_admin(): void
    {
        Mail::fake();

        $this->post('/inquiry', [
            'type' => 'contact',
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'message' => 'I need a school management system.',
        ])->assertRedirect(route('inquiries.thanks'));

        $this->assertDatabaseHas('inquiries', ['email' => 'customer@example.com', 'type' => 'contact', 'status' => 'new']);
        Mail::assertSent(NewInquiryMail::class);
    }

    public function test_order_requires_phone_and_validates_type(): void
    {
        $this->post('/inquiry', ['type' => 'order', 'name' => 'A', 'email' => 'a@example.com'])
            ->assertSessionHasErrors('phone');

        $this->post('/inquiry', ['type' => 'hacker', 'name' => 'A', 'email' => 'a@example.com', 'message' => 'x'])
            ->assertSessionHasErrors('type');

        $this->assertSame(0, Inquiry::count());
    }

    public function test_honeypot_silently_drops_spam(): void
    {
        $this->post('/inquiry', [
            'type' => 'contact', 'name' => 'Bot', 'email' => 'bot@example.com',
            'message' => 'spam', 'website' => 'http://spam.example',
        ])->assertRedirect(route('inquiries.thanks'));

        $this->assertSame(0, Inquiry::count());
    }

    public function test_newsletter_subscription_is_idempotent(): void
    {
        $this->post('/subscribe', ['email' => 'Reader@Example.com'])->assertSessionHas('subscribed');
        $this->post('/subscribe', ['email' => 'reader@example.com'])->assertSessionHas('subscribed');

        $this->assertSame(1, Subscriber::where('email', 'reader@example.com')->count());
    }

    public function test_sitemap_robots_and_feed(): void
    {
        $this->get('/sitemap.xml')->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('products.show', 'cloud-pos-inventory'), false)
            ->assertSee(route('blog.show', 'how-to-start-an-online-business-in-bangladesh-in-2026'), false);

        $this->get('/robots.txt')->assertOk()->assertSee('User-agent');
        $this->get('/blog/feed')->assertOk()->assertSee('<rss', false);
    }

    public function test_settings_changes_show_on_site(): void
    {
        \App\Models\Setting::setMany(['hero_title' => 'Brand new hero headline', 'phone' => '01999999999']);

        $this->get('/')->assertSee('Brand new hero headline')->assertSee('01999999999');
    }
}

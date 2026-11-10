<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Portfolio;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ProjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_fresh_install_gets_the_real_projects_with_their_screenshots(): void
    {
        Storage::fake('public');

        $this->seed(DatabaseSeeder::class);

        $this->assertSame(12, Portfolio::count());
        $this->assertSame(10, Client::count());
        $this->assertDatabaseMissing('portfolios', ['slug' => 'freshmart-online-grocery']);
        $this->assertDatabaseMissing('clients', ['name' => 'FreshMart']);

        $metrobox = Portfolio::where('slug', 'metrobox-bangladesh')->firstOrFail();
        $this->assertSame('https://metroboxbd.com/', $metrobox->project_url);
        Storage::disk('public')->assertExists($metrobox->thumbnail);
        Storage::disk('public')->assertExists($metrobox->gallery[0]);

        $this->get('/portfolio')->assertOk()->assertSee('Smart Order Solution (eSky ERP)')->assertSee('Zara Build');
        $this->get('/portfolio/metrobox-bangladesh')->assertOk()->assertSee('https://metroboxbd.com/');
        $this->get('/')->assertOk()->assertSee('Metrobox Bangladesh');
    }

    public function test_seeding_again_adds_nothing_and_keeps_admin_edits(): void
    {
        Storage::fake('public');
        $this->seed(ProjectSeeder::class);

        Portfolio::where('slug', 'zara-build')->update(['title' => 'Zara Build — edited by admin']);

        $this->seed(ProjectSeeder::class);

        $this->assertSame(12, Portfolio::count());
        $this->assertSame(10, Client::count());
        $this->assertDatabaseHas('portfolios', ['slug' => 'zara-build', 'title' => 'Zara Build — edited by admin']);
    }

    public function test_an_old_install_loses_only_untouched_demo_rows(): void
    {
        Storage::fake('public');
        Portfolio::create(['title' => 'FreshMart Online Grocery', 'slug' => 'freshmart-online-grocery']);
        Portfolio::create(['title' => 'EduCare School Portal', 'slug' => 'educare-school-portal', 'project_url' => 'https://real-client.example']);
        Client::create(['name' => 'FreshMart']);

        $this->seed(ProjectSeeder::class);

        $this->assertDatabaseMissing('portfolios', ['slug' => 'freshmart-online-grocery']);
        $this->assertDatabaseHas('portfolios', ['slug' => 'educare-school-portal']);
        $this->assertDatabaseMissing('clients', ['name' => 'FreshMart']);
    }
}

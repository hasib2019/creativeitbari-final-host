<?php

namespace Tests\Feature;

use App\Filament\Pages\SiteSettings;
use App\Models\Setting;
use App\Models\User;
use App\Support\Favicon;
use Filament\Forms\Components\FileUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FaviconTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->create(['is_admin' => true]));
    }

    public function test_saving_a_favicon_builds_every_size_and_the_ico(): void
    {
        Livewire::test(SiteSettings::class)
            ->fillForm(['favicon' => UploadedFile::fake()->image('icon.png', 600, 600)])
            ->call('save')
            ->assertHasNoFormErrors();

        $set = Setting::get('favicon_set');
        $this->assertSame(Setting::get('favicon'), $set['source']);

        foreach (Favicon::SIZES as $file => $size) {
            $bytes = Storage::disk('public')->get($set['dir'].'/'.$file);
            [$w, $h] = getimagesizefromstring($bytes);
            $this->assertSame([$size, $size], [$w, $h], $file);
        }

        $ico = Storage::disk('public')->get($set['dir'].'/favicon.ico');
        $this->assertSame([0, 1, 3], array_values(unpack('v3', substr($ico, 0, 6))), 'ICO header: reserved, type icon, 3 images');

        $this->get('/')
            ->assertOk()
            ->assertSee('rel="apple-touch-icon"', false)
            ->assertSee($set['dir'].'/favicon-32.png', false)
            ->assertSee(route('manifest'), false);

        $this->get('/favicon.ico')->assertOk()->assertHeader('Content-Type', 'image/x-icon');
        $this->get('/site.webmanifest')->assertOk()->assertJsonCount(2, 'icons');
    }

    public function test_a_non_square_image_is_cropped_not_squashed(): void
    {
        Storage::disk('public')->put('settings/wide.png', UploadedFile::fake()->image('wide.png', 900, 300)->getContent());

        $set = Favicon::generate('settings/wide.png');

        [$w, $h] = getimagesizefromstring(Storage::disk('public')->get($set['dir'].'/icon-512.png'));
        $this->assertSame([512, 512], [$w, $h]);
    }

    public function test_the_upload_field_crops_to_a_square_and_resizes_to_512(): void
    {
        $field = Livewire::test(SiteSettings::class)->instance()->form->getComponent(
            fn ($component) => $component instanceof FileUpload && $component->getName() === 'favicon',
            withHidden: true,
        );

        $this->assertInstanceOf(FileUpload::class, $field);

        $this->assertSame('1:1', $field->getImageAspectRatio());
        $this->assertTrue($field->shouldAutomaticallyCropImagesToAspectRatio());
        $this->assertSame('1:1', $field->getAutomaticallyCropImagesAspectRatio());
        $this->assertSame('cover', $field->getAutomaticallyResizeImagesMode());
        $this->assertSame('512', (string) $field->getAutomaticallyResizeImagesWidth());
        $this->assertSame('512', (string) $field->getAutomaticallyResizeImagesHeight());
    }

    public function test_removing_the_favicon_clears_the_sizes(): void
    {
        Livewire::test(SiteSettings::class)
            ->fillForm(['favicon' => UploadedFile::fake()->image('icon.png', 300, 300)])
            ->call('save');
        $dir = Setting::get('favicon_set')['dir'];

        Livewire::test(SiteSettings::class)
            ->fillForm(['favicon' => null])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertNull(Setting::get('favicon_set'));
        Storage::disk('public')->assertMissing($dir.'/favicon-32.png');
        $this->get('/favicon.ico')->assertNoContent();
    }
}

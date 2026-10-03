<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomeSliderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_and_remove_home_slider_images(): void
    {
        Storage::fake('public');
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        Cache::put('home_settings_map', ['stale' => true], 60);

        $this->actingAs($admin)
            ->get(route('admin.sliders.index'))
            ->assertOk()
            ->assertSee('slider-1.webp');

        $this->post(route('admin.sliders.update'), [
                'slider_images' => [UploadedFile::fake()->create('banner.jpg', 50, 'image/jpeg')],
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $sliderImages = json_decode(Setting::get('home_slider_images'), true);
        $this->assertCount(count(Setting::HOME_SLIDER_DEFAULTS) + 1, $sliderImages);
        $uploadedPath = $sliderImages[count(Setting::HOME_SLIDER_DEFAULTS)]['desktop'];
        $this->assertStringStartsWith('sliders/', $uploadedPath);
        $this->assertTrue(Storage::disk('public')->exists($uploadedPath));
        $this->assertFalse(Cache::has('home_settings_map'));
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(asset('storage/' . $uploadedPath), false);

        $this->post(route('admin.sliders.update'), [
            'remove_slider_images' => [count(Setting::HOME_SLIDER_DEFAULTS)],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertCount(count(Setting::HOME_SLIDER_DEFAULTS), json_decode(Setting::get('home_slider_images'), true));
        $this->assertFalse(Storage::disk('public')->exists($uploadedPath));
    }

    public function test_home_settings_control_slider_visibility_without_image_upload_controls(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('home_show_slider')
            ->assertDontSee('slider_images');

        $this->post(route('admin.settings.update'), ['submitted' => '1'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('0', Setting::get('home_show_slider'));
        $this->get(route('home'))->assertOk()->assertDontSee('id="heroCarousel"', false);
    }
}
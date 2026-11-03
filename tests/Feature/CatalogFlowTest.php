<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductReport;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CatalogFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_must_log_in_before_viewing_catalog_and_registration_is_available(): void
    {
        $this->get(route('catalog.index'))->assertRedirect(route('login'));
        $this->get(route('register'))->assertOk();
        $this->get(route('catalog.create'))->assertRedirect(route('login'));
    }

    public function test_guest_must_log_in_before_viewing_a_product_page(): void
    {
        $product = Product::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Produk',
            'description' => 'Deskripsi produk.',
            'category' => 'Lainnya',
        ]);

        $this->get(route('catalog.show', $product))->assertRedirect(route('login'));
    }

    public function test_catalog_cards_show_product_details_and_a_map_link(): void
    {
        $product = Product::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Roti artisan',
            'description' => 'Roti dibuat segar setiap hari.',
            'category' => 'Makanan',
            'price' => 25000,
            'location_address' => 'Jl. Merdeka No. 10, Jakarta',
        ]);

        $this->actingAs(User::factory()->create())->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('Roti artisan')
            ->assertSee('Rp 25.000')
            ->assertSee('Roti dibuat segar setiap hari.')
            ->assertSee('Jl. Merdeka No. 10, Jakarta')
            ->assertSee('Lihat lokasi')
            ->assertSee('Laporkan');
    }

    public function test_registration_login_and_logout_work_without_granting_admin_access(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Pembuat Katalog',
            'email' => 'seller@example.test',
            'password' => 'strong-password',
            'password_confirmation' => 'strong-password',
        ])->assertRedirect(route('catalog.index'));

        $user = User::where('email', 'seller@example.test')->firstOrFail();
        $this->assertFalse($user->is_admin);
        $this->assertTrue(Hash::check('strong-password', $user->password));

        $this->post(route('logout'))->assertRedirect(route('catalog.index'));
        $this->post(route('login.store'), [
            'email' => 'seller@example.test',
            'password' => 'strong-password',
        ])->assertRedirect(route('catalog.index'));
    }

    public function test_seeder_creates_admin_only_when_configured(): void
    {
        config(['app.admin' => [
            'name' => 'Admin TEFA',
            'email' => 'admin@example.test',
            'password' => 'admin-secret-password',
        ]]);

        $this->seed(DatabaseSeeder::class);
        $admin = User::where('email', 'admin@example.test')->firstOrFail();

        $this->assertTrue($admin->is_admin);
        $this->assertTrue(Hash::check('admin-secret-password', $admin->password));
    }

    public function test_registered_seller_can_publish_a_catalog_with_multiple_images(): void
    {
        Storage::fake('public');
        $seller = User::factory()->create();

        $response = $this->actingAs($seller)->post(route('catalog.store'), [
            'title' => 'Roti artisan',
            'description' => 'Roti dibuat segar setiap hari.',
            'category' => 'Makanan',
            'price' => '25000',
            'location_address' => 'Jl. Merdeka No. 10, Jakarta',
            'location_latitude' => '-6.2000000',
            'location_longitude' => '106.8166660',
            'images' => [
                UploadedFile::fake()->createWithContent('roti-1.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/rXcAAAAASUVORK5CYII=')),
                UploadedFile::fake()->createWithContent('roti-2.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/rXcAAAAASUVORK5CYII=')),
                UploadedFile::fake()->createWithContent('roti-3.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/rXcAAAAASUVORK5CYII=')),
            ],
        ]);

        $product = Product::firstOrFail();
        $response->assertRedirect(route('catalog.show', $product));
        $this->assertDatabaseCount('product_images', 3);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'location_address' => 'Jl. Merdeka No. 10, Jakarta',
            'location_latitude' => '-6.2000000',
            'location_longitude' => '106.8166660',
        ]);
        $this->get(route('catalog.show', $product))
            ->assertOk()
            ->assertSee('Roti artisan')
            ->assertSee('Jl. Merdeka No. 10, Jakarta')
            ->assertSee('https://www.google.com/maps/search/?api=1&amp;query=-6.2000000%2C106.8166660', false);
        foreach ($product->images as $image) {
            Storage::disk('public')->assertExists($image->path);
        }
    }

    public function test_users_can_report_products_and_admin_can_review_reports(): void
    {
        $seller = User::factory()->create();
        $reporter = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $product = Product::create([
            'user_id' => $seller->id,
            'title' => 'Produk',
            'description' => 'Deskripsi produk.',
            'category' => 'Lainnya',
        ]);

        $this->actingAs($reporter)->get(route('catalog.show', $product))
            ->assertOk()
            ->assertSee('Barang berbahaya')
            ->assertSee('Barang ilegal');

        $this->actingAs($reporter)
            ->post(route('reports.store', $product), [
                'reason' => 'barang_ilegal',
                'details' => 'Produk diduga tidak sesuai aturan.',
            ])
            ->assertRedirect();

        $this->actingAs($reporter)
            ->post(route('reports.store', $product), ['reason' => 'barang_berbahaya'])
            ->assertRedirect();

        $report = ProductReport::where('reason', 'barang_ilegal')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.index'))
            ->assertOk()
            ->assertSee('Dashboard admin')
            ->assertSee('Barang ilegal')
            ->assertSee('Barang berbahaya');
        $this->actingAs($admin)
            ->patch(route('admin.reports.update', $report), ['status' => 'reviewed'])
            ->assertRedirect();

        $this->assertDatabaseHas('product_reports', ['id' => $report->id, 'status' => 'reviewed']);
        $this->actingAs($reporter)->get(route('admin.index'))->assertForbidden();
    }

    public function test_admin_can_delete_a_reported_catalog_and_keep_its_report_history(): void
    {
        Storage::fake('public');
        $seller = User::factory()->create();
        $reporter = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $product = Product::create([
            'user_id' => $seller->id,
            'title' => 'Produk berbahaya',
            'description' => 'Produk yang dilaporkan.',
            'category' => 'Lainnya',
        ]);
        $imagePath = 'catalogs/reported-product.png';
        Storage::disk('public')->put($imagePath, 'product image');
        $product->images()->create(['path' => $imagePath]);
        $report = ProductReport::create([
            'user_id' => $reporter->id,
            'product_id' => $product->id,
            'reason' => 'barang_berbahaya',
            'details' => 'Mohon ditinjau.',
        ]);

        $this->actingAs($admin)->get(route('admin.index'))
            ->assertOk()
            ->assertSee('Produk berbahaya')
            ->assertSee('Hapus katalog')
            ->assertSee('Mohon ditinjau.');

        $this->delete(route('admin.products.destroy', $product))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseHas('product_reports', [
            'id' => $report->id,
            'product_id' => null,
            'status' => 'reviewed',
        ]);
        Storage::disk('public')->assertMissing($imagePath);

        $this->actingAs($admin)->get(route('admin.index'))
            ->assertOk()
            ->assertSee('Katalog sudah dihapus')
            ->assertSee('Ditinjau');
    }

    public function test_admin_is_read_only_on_catalogs_and_manages_them_from_admin_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $product = Product::create([
            'user_id' => $admin->id,
            'title' => 'Katalog admin lama',
            'description' => 'Deskripsi produk.',
            'category' => 'Lainnya',
        ]);

        $this->actingAs($admin)
            ->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('Dashboard admin')
            ->assertDontSee('Buat katalog');
        $this->get(route('catalog.show', $product))
            ->assertOk()
            ->assertDontSee('Edit katalog')
            ->assertDontSee('Hapus');
        $this->get(route('catalog.create'))->assertForbidden();
        $this->get(route('catalog.edit', $product))->assertForbidden();
        $this->put(route('catalog.update', $product), [
            'title' => 'Diubah admin',
            'description' => 'Deskripsi produk.',
            'category' => 'Lainnya',
        ])->assertForbidden();
        $this->delete(route('catalog.destroy', $product))->assertForbidden();
        $this->get(route('admin.index'))->assertOk();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'title' => 'Katalog admin lama',
        ]);
    }

    public function test_seller_cannot_report_own_product_or_edit_another_sellers_catalog(): void
    {
        $seller = User::factory()->create();
        $otherUser = User::factory()->create();
        $product = Product::create([
            'user_id' => $seller->id,
            'title' => 'Produk',
            'description' => 'Deskripsi produk.',
            'category' => 'Lainnya',
        ]);

        $this->actingAs($seller)
            ->post(route('reports.store', $product), ['reason' => 'penipuan'])
            ->assertForbidden();
        $this->actingAs($otherUser)
            ->get(route('catalog.edit', $product))
            ->assertForbidden();
    }
}

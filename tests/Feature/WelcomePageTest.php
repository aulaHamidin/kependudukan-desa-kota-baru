<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SeedMasterData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class WelcomePageTest extends TestCase
{
    use PolicyTestHelper;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-06-15 10:00:00'));
        $this->seed(SeedMasterData::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_guest_can_view_welcome_page_with_database_stats(): void
    {
        $territory = $this->createTerritory();
        $actor = User::factory()->create([
            'role' => 'admin_desa',
            'desa_id' => $territory['desa']->id,
        ]);

        DB::table('penduduks')->insert([
            'nik' => '1234567890123456',
            'nama_lengkap' => 'Penduduk Public Stats',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Kota Test',
            'tgl_lahir' => '2000-01-01',
            'agama_id' => 'ISLAM',
            'pendidikan_id' => 'SD',
            'pekerjaan_id' => 'BELUM',
            'pendapatan_range_id' => null,
            'golongan_darah_id' => null,
            'kewarganegaraan' => 'WNI',
            'status_perkawinan' => 'Belum Kawin',
            'rt_id' => $territory['rt']->id,
            'status_kependudukan_code' => 'AKTIF',
            'tanggal_status' => now()->toDateString(),
            'data_version' => 1,
            'created_by' => $actor->id,
            'updated_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('welcome');
        $response->assertViewHas('pendudukStats', fn (array $stats): bool => $stats['aktif'] === 1);
    }

    public function test_authenticated_user_is_redirected_to_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect(route('dashboard'));
    }
}

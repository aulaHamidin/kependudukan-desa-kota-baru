<?php

namespace Tests\Feature\Auth;

use App\Models\Penduduk;
use App\Providers\RouteServiceProvider;
use Database\Seeders\SeedMasterData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\PolicyTestHelper;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase, PolicyTestHelper;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $this->seed(SeedMasterData::class);

        $territory = $this->createTerritory();
        $actor     = $this->adminDesa($territory['desa']);

        $nik = '1234567890123456';

        Penduduk::factory()->create([
            'rt_id'                    => $territory['rt']->id,
            'nik'                      => $nik,
            'status_kependudukan_code' => 'AKTIF',
            'created_by'               => $actor->id,
        ]);

        $response = $this->post('/register', [
            'nik'                   => $nik,
            'email'                 => 'viewer@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'terms_accepted'        => true,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }
}

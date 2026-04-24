<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserImpersonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_impersonate_when_setting_is_enabled(): void
    {
        DB::table('settings')->insert([
            'key' => 'allow_user_impersonation',
            'value' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['role' => 'staff']);

        $this->actingAs($admin)
            ->postJson("/api/users/{$target->id}/impersonate")
            ->assertOk()
            ->assertJsonFragment([
                'message' => 'Now impersonating '.$target->name,
            ]);
    }

    public function test_admin_cannot_impersonate_when_setting_is_disabled(): void
    {
        DB::table('settings')->insert([
            'key' => 'allow_user_impersonation',
            'value' => '0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['role' => 'staff']);

        $this->actingAs($admin)
            ->postJson("/api/users/{$target->id}/impersonate")
            ->assertForbidden()
            ->assertJsonFragment([
                'message' => 'User impersonation is disabled by system settings.',
            ]);
    }
}

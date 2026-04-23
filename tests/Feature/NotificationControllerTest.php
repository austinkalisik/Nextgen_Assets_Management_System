<?php

namespace Tests\Feature;

use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_filter_notifications_by_status_priority_type_and_search(): void
    {
        $user = $this->user();

        SystemNotification::create([
            'user_id' => $user->id,
            'type' => 'inventory_alert',
            'priority' => 'high',
            'title' => 'Low Stock Warning',
            'message' => 'Radio stock is low.',
            'url' => '/inventory',
        ]);

        SystemNotification::create([
            'user_id' => $user->id,
            'type' => 'asset_updated',
            'priority' => 'normal',
            'title' => 'Asset Updated',
            'message' => 'Laptop was updated.',
            'url' => '/items',
            'read_at' => now(),
        ]);

        $response = $this->actingAs($user)->getJson('/api/notifications?status=unread&priority=high&type=inventory_alert&search=radio');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Low Stock Warning')
            ->assertJsonPath('data.0.priority', 'high');
    }

    public function test_user_can_fetch_notification_stats(): void
    {
        $user = $this->user();

        SystemNotification::create([
            'user_id' => $user->id,
            'type' => 'inventory_alert',
            'priority' => 'high',
            'title' => 'Low Stock Warning',
            'message' => 'Radio stock is low.',
        ]);

        SystemNotification::create([
            'user_id' => $user->id,
            'type' => 'asset_updated',
            'priority' => 'normal',
            'title' => 'Asset Updated',
            'message' => 'Laptop was updated.',
            'read_at' => now(),
        ]);

        $this->actingAs($user)
            ->getJson('/api/notifications/stats')
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('unread', 1)
            ->assertJsonPath('read', 1)
            ->assertJsonPath('high_priority_unread', 1)
            ->assertJsonPath('by_priority.high', 1)
            ->assertJsonPath('by_type.inventory_alert', 1);
    }

    public function test_user_can_delete_own_notification_and_clear_read_notifications(): void
    {
        $user = $this->user();

        $unread = SystemNotification::create([
            'user_id' => $user->id,
            'type' => 'inventory_alert',
            'priority' => 'high',
            'title' => 'Low Stock Warning',
            'message' => 'Radio stock is low.',
        ]);

        $read = SystemNotification::create([
            'user_id' => $user->id,
            'type' => 'asset_updated',
            'priority' => 'normal',
            'title' => 'Asset Updated',
            'message' => 'Laptop was updated.',
            'read_at' => now(),
        ]);

        $this->actingAs($user)
            ->deleteJson("/api/notifications/{$unread->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Notification deleted.');

        $this->assertDatabaseMissing('system_notifications', [
            'id' => $unread->id,
        ]);

        $this->actingAs($user)
            ->deleteJson('/api/notifications/read')
            ->assertOk()
            ->assertJsonPath('deleted', 1);

        $this->assertDatabaseMissing('system_notifications', [
            'id' => $read->id,
        ]);
    }

    protected function user(): User
    {
        return User::create([
            'name' => 'Notification User',
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);
    }
}

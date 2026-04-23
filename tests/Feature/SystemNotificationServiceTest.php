<?php

namespace Tests\Feature;

use App\Models\SystemNotification;
use App\Models\User;
use App\Services\SystemNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SystemNotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_notify_admins_excludes_the_actor_by_default(): void
    {
        $actor = $this->user('Actor Admin', 'admin');
        $otherAdmin = $this->user('Other Admin', 'admin');
        $staff = $this->user('Staff User', 'staff');

        $this->actingAs($actor);

        app(SystemNotificationService::class)->notifyAdmins(
            'asset_updated',
            'Asset Updated',
            'Asset radio was updated.',
            '/items',
            'item',
            10
        );

        $this->assertDatabaseMissing('system_notifications', [
            'user_id' => $actor->id,
            'type' => 'asset_updated',
        ]);

        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $otherAdmin->id,
            'type' => 'asset_updated',
            'priority' => 'normal',
            'title' => 'Asset Updated',
            'source_type' => 'item',
            'source_id' => 10,
        ]);

        $this->assertDatabaseMissing('system_notifications', [
            'user_id' => $staff->id,
            'type' => 'asset_updated',
        ]);
    }

    public function test_notify_user_excludes_the_actor_by_default(): void
    {
        $actor = $this->user('Assigned User', 'staff');

        $this->actingAs($actor);

        app(SystemNotificationService::class)->notifyUser(
            $actor->id,
            'assignment_created',
            'Asset Assigned To You',
            'One unit was assigned to you.',
            '/assignments',
            'assignment',
            20
        );

        $this->assertSame(0, SystemNotification::count());
    }

    public function test_notify_user_can_notify_a_different_user(): void
    {
        $actor = $this->user('System Administrator', 'admin');
        $receiver = $this->user('Receiver', 'staff');

        $this->actingAs($actor);

        app(SystemNotificationService::class)->notifyUser(
            $receiver->id,
            'assignment_created',
            'Asset Assigned To You',
            'One unit was assigned to you.',
            '/assignments',
            'assignment',
            20
        );

        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $receiver->id,
            'type' => 'assignment_created',
            'priority' => 'normal',
            'source_type' => 'assignment',
            'source_id' => 20,
        ]);
    }

    public function test_notification_priority_is_inferred_for_critical_inventory_events(): void
    {
        $actor = $this->user('Asset Officer', 'asset_officer');
        $admin = $this->user('System Administrator', 'admin');

        $this->actingAs($actor);

        app(SystemNotificationService::class)->notifyAdmins(
            'inventory_alert',
            'Inventory Alert',
            'Stock is below the required threshold.',
            '/inventory',
            'item',
            7
        );

        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $admin->id,
            'type' => 'inventory_alert',
            'priority' => 'high',
        ]);
    }

    protected function user(string $name, string $role): User
    {
        return User::create([
            'name' => $name,
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }
}

<?php

namespace App\Services;

use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class SystemNotificationService
{
    public function notifyAdmins(
        string $type,
        string $title,
        string $message,
        ?string $url = null,
        ?string $sourceType = null,
        ?int $sourceId = null,
        ?int $excludeUserId = null,
        ?string $priority = null,
        ?array $data = null
    ): void {
        $excludeUserId = $excludeUserId ?? Auth::id();
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            if ($excludeUserId && (int) $admin->id === (int) $excludeUserId) {
                continue;
            }

            SystemNotification::create([
                'user_id' => $admin->id,
                'type' => $type,
                'priority' => $priority ?? $this->priorityForType($type),
                'title' => $title,
                'message' => $message,
                'url' => $url,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'data' => $data,
            ]);
        }
    }

    public function notifyUser(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?string $url = null,
        ?string $sourceType = null,
        ?int $sourceId = null,
        ?int $excludeUserId = null,
        ?string $priority = null,
        ?array $data = null
    ): void {
        $excludeUserId = $excludeUserId ?? Auth::id();

        if ($excludeUserId && $userId === $excludeUserId) {
            return;
        }

        SystemNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'priority' => $priority ?? $this->priorityForType($type),
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'data' => $data,
        ]);
    }

    public function notifyUsers(
        Collection|array $userIds,
        string $type,
        string $title,
        string $message,
        ?string $url = null,
        ?string $sourceType = null,
        ?int $sourceId = null,
        ?int $excludeUserId = null,
        ?string $priority = null,
        ?array $data = null
    ): void {
        foreach (collect($userIds)->unique() as $userId) {
            $this->notifyUser(
                (int) $userId,
                $type,
                $title,
                $message,
                $url,
                $sourceType,
                $sourceId,
                $excludeUserId,
                $priority,
                $data
            );
        }
    }

    protected function priorityForType(string $type): string
    {
        return match ($type) {
            'assignment_overdue',
            'asset_deleted',
            'inventory_alert' => 'high',
            'low_stock',
            'maintenance_due' => 'medium',
            'assignment_created',
            'assignment_returned',
            'asset_created',
            'asset_updated',
            'settings_updated',
            'user_created',
            'user_updated' => 'normal',
            default => 'low',
        };
    }
}

<?php

namespace App\Services;

use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Support\Collection;

class SystemNotificationService
{
    public function notifyAdmins(
        string $type,
        string $title,
        string $message,
        ?string $url = null,
        ?string $sourceType = null,
        ?int $sourceId = null
    ): void {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            SystemNotification::create([
                'user_id' => $admin->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'url' => $url,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
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
        ?int $sourceId = null
    ): void {
        SystemNotification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
        ]);
    }

    public function notifyUsers(
        Collection|array $userIds,
        string $type,
        string $title,
        string $message,
        ?string $url = null,
        ?string $sourceType = null,
        ?int $sourceId = null
    ): void {
        foreach (collect($userIds)->unique() as $userId) {
            $this->notifyUser(
                (int) $userId,
                $type,
                $title,
                $message,
                $url,
                $sourceType,
                $sourceId
            );
        }
    }
}
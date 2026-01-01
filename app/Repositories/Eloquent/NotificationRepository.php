<?php

namespace App\Repositories\Eloquent;

use App\Models\Notification;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function query(): Builder
    {
        return Notification::query()
            ->with('user')
            ->orderByDesc('created_at');
    }

    public function create(array $data): Notification
    {
        return Notification::create($data);
    }

    public function find(int $id): ?Notification
    {
        return Notification::with('user')->find($id);
    }

    public function setReadState(int $id, bool $isRead): bool
    {
        $notification = Notification::find($id);

        return $notification ? $notification->update(['is_read' => $isRead]) : false;
    }

    public function delete(int $id): bool
    {
        $notification = Notification::find($id);

        return $notification ? (bool) $notification->delete() : false;
    }
}

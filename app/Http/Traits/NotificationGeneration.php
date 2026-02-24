<?php

namespace App\Http\Traits;

use App\Models\Notification;

trait NotificationGeneration
{
    public function createNotificstion(array $notification)
    {
        Notification::create($notification);
    }
}
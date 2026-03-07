<?php

namespace App\Listeners;

use App\Events\TokenCreated;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LogTokenCreation
{
    public function handle(TokenCreated $event): void
    {
        $user =User::find($event->userId);
        Log::info('Token created', [
            'user_id' => $event->userId,
            'created_at' => $event->createdAt,
            'user_name' => $user ? $user->name : null
        ]);
    }
}
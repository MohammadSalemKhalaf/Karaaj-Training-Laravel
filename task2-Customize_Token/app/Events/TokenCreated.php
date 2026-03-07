<?php

namespace App\Events;

class TokenCreated
{
    public int $userId;
    public string $createdAt;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
        $this->createdAt = now()->toDateTimeString();
    }
}
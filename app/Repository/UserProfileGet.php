<?php

namespace BadHabit\LoginManagement\Repository;

use BadHabit\LoginManagement\Service\SessionService;

class UserProfileGet
{
    public function __construct(private SessionService $sessionService)
    {
    }

    public function getAllUserProfile(): array
    {
        return [
            "username" => $this->sessionService->current()->username,
            "full_name" => $this->sessionService->current()->full_name,
            "email" => $this->sessionService->current()->email,
            "role" => $this->sessionService->current()->role
        ];
    }

    public function getOnlyUserProfile(string $params): array
    {
        return [
            $params => $this->sessionService->current()->{$params},
        ];
    }

}
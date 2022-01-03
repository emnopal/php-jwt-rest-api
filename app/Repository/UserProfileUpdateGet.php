<?php

namespace BadHabit\LoginManagement\Repository;

use BadHabit\LoginManagement\Model\UserProfileUpdateIDResponse;

class UserProfileUpdateGet
{
    public function __construct(private UserProfileUpdateIDResponse $response)
    {
    }

    public function getUpdateData(): array
    {
        $data = [
            'username' => $this->response->user->username,
            'full_name' => $this->response->user->full_name,
            'email' => $this->response->user->email,
            'role' => $this->response->user->role
        ];
    }
}
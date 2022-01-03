<?php

namespace BadHabit\LoginManagement\Model;

class UserProfileUpdateRequest
{
    public ?string $username = null;
    public ?string $fullName = null;
    public ?string $email = null;
}
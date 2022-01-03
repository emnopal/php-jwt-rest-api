<?php

namespace BadHabit\LoginManagement\Model;

class UserLoginRequest
{
    public ?string $username = null;
    public ?string $password = null;
    public string $role = 'user';
}
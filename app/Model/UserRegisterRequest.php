<?php

namespace BadHabit\LoginManagement\Model;

class UserRegisterRequest
{
    public ?string $username = null;
    public ?string $password = null;
    public ?string $fullName = null;
    public ?string $email = null;
    public string $role = 'user';
}
<?php

namespace BadHabit\LoginManagement\Domain;

class User
{
    public string $username;
    public string $password;
    public string $full_name;
    public string $email;
    public string $role = 'user';
}
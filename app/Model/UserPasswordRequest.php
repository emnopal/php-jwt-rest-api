<?php

namespace BadHabit\LoginManagement\Model;

class UserPasswordRequest
{
    public ?string $username = null;
    public ?string $old = null;
    public ?string $new = null;
}
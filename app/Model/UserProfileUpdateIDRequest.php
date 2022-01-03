<?php

namespace BadHabit\LoginManagement\Model;

class UserProfileUpdateIDRequest
{
    public ?string $old_username = null;
    public ?string $new_username = null;
}
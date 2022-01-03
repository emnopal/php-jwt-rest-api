<?php

namespace BadHabit\LoginManagement\Model;

use BadHabit\LoginManagement\Domain\Encode;

class EncodedSession
{
    public string $key;
    public string $issuedAt;
    public string $expireAt;
    public Encode $data;
}
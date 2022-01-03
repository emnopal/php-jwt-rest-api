<?php

namespace BadHabit\LoginManagement\Repository;

use BadHabit\LoginManagement\Helper\TimestampConv;
use BadHabit\LoginManagement\Model\EncodedSession;
use BadHabit\LoginManagement\Model\UserLoginResponse;

class LoggedInSessionData
{

    public function __construct(
        private string $key
    )
    {
    }

    public function sessionData(): array
    {
        return [
            "session_data" => [
                "key" => $this->key,
            ]
        ];
    }
}
<?php

namespace BadHabit\LoginManagement\Repository;

use BadHabit\LoginManagement\Auth\Handler;
use BadHabit\LoginManagement\Domain\Decode;
use BadHabit\LoginManagement\Helper\TimestampConv;
use BadHabit\LoginManagement\Service\SessionService;

class UserGetSession
{
    public function __construct(private string $cookie, private SessionService $sessionService)
    {
    }

    public function getSession(): array
    {
        return [
            "session_key" => $this->cookie
        ];
    }

    public function getSessionDecrypt(): array
    {
        $decode = new Decode();
        $decode->token = $this->cookie;
        $handler = new Handler();
        $decoded = new SessionRepository($handler);
        $decoded_result = $decoded->decodeToken($decode)->payload;
        $userProfileGet = new UserProfileGet($this->sessionService);
        return [
            "session_key" => $this->cookie,
            "session_data" => [
                "who_issued" => $decoded_result->iss,
                "issued_at_timestamp" => $decoded_result->iat,
                "issued_at" => TimestampConv::readableTimestamp($decoded_result->iat),
                "expires_at_timestamp" => $decoded_result->exp,
                "expires_at" => TimestampConv::readableTimestamp($decoded_result->exp),
                "data" => $decoded_result->data
            ],
            "user_data" => $userProfileGet->getAllUserProfile()
        ];
    }
}
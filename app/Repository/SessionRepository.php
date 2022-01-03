<?php

namespace BadHabit\LoginManagement\Repository;

use BadHabit\LoginManagement\Auth\Handler;
use BadHabit\LoginManagement\Domain\UserSession;
use BadHabit\LoginManagement\Model\DecodedSession;
use BadHabit\LoginManagement\Domain\Decode;
use BadHabit\LoginManagement\Domain\Encode;
use BadHabit\LoginManagement\Model\EncodedSession;

class SessionRepository
{

    private ?string $url;
    private Handler $handler;

    public function __construct(Handler $handler, ?string $url = null)
    {
        $this->handler = $handler;

        if (!$url) {
            if (!isset($_SERVER['HTTP_HOST']) && !isset($_SERVER['REQUEST_URI'])) {
                $this->url = "https://example.com";
            } else {
                $this->url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            }
        } else {
            $this->url = $url;
        }
    }

    public function getToken(UserSession $userSession): EncodedSession
    {
        $encode = new Encode();
        $encode->iss = $this->url;
        $encode->userSession = $userSession;

        return $this->handler->encode($encode);
    }

    public function decodeToken(Decode $decode): DecodedSession
    {
        return $this->handler->decode($decode);
    }

}
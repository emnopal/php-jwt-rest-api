<?php

namespace BadHabit\LoginManagement\App;

use BadHabit\LoginManagement\Auth\Handler;
use BadHabit\LoginManagement\Domain\UserSession;
use BadHabit\LoginManagement\Model\DecodedSession;
use BadHabit\LoginManagement\Domain\Decode;
use BadHabit\LoginManagement\Domain\Encode;
use BadHabit\LoginManagement\Model\EncodedSession;
use PHPUnit\Framework\TestCase;

class HandlerTest extends TestCase
{


    public function testEncode()
    {
        // Set user session
        $userSession = new UserSession();
        $userSession->user_id = "test";
        $userSession->email = "test@mail.com";
        $userSession->role = "user";

        // Encode Session
        $encode = new Encode();
        $encode->iss = 'https://example.com';
        $encode->userSession = $userSession;

        $handler = new Handler();
        self::assertInstanceOf(EncodedSession::class, $handler->encode($encode));
        self::assertEquals($encode->iss, $handler->encode($encode)->data->iss);
        self::assertEquals($userSession->user_id, $handler->encode($encode)->data->userSession->user_id);
        self::assertEquals($userSession->email, $handler->encode($encode)->data->userSession->email);
        self::assertEquals($userSession->role, $handler->encode($encode)->data->userSession->role);
    }

    public function testDecode()
    {
        // Set user session
        $userSession = new UserSession();
        $userSession->user_id = "test";
        $userSession->email = "test@mail.com";
        $userSession->role = "user";

        // Encode Session
        $encode = new Encode();
        $encode->iss = 'https://example.com';
        $encode->userSession = $userSession;

        $handler = new Handler();
        $token = $handler->encode($encode);

        $decode = new Decode();
        $decode->token = $token->key;

        $decoded = $handler->decode($decode);

        self::assertInstanceOf(DecodedSession::class, $decoded);
        self::assertEquals($encode->iss, $handler->encode($encode)->data->iss);
        self::assertEquals($userSession->user_id, $decoded->payload->data->user_id);
        self::assertEquals($userSession->email, $decoded->payload->data->email);
        self::assertEquals($userSession->role, $decoded->payload->data->role);
    }
}

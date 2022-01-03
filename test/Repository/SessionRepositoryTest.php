<?php

namespace BadHabit\LoginManagement\Repository;

use BadHabit\LoginManagement\Auth\Handler;
use BadHabit\LoginManagement\Domain\UserSession;
use BadHabit\LoginManagement\Model\DecodedSession;
use BadHabit\LoginManagement\Domain\Decode;
use BadHabit\LoginManagement\Model\EncodedSession;
use BadHabit\LoginManagement\Service\SessionService;
use PHPUnit\Framework\TestCase;

class SessionRepositoryTest extends TestCase
{

    private SessionRepository $sessionRepository;
    private SessionService $sessionService;

    public function setUp(): void
    {
        $this->sessionRepository = new SessionRepository(new Handler());
    }

    public function testGetToken()
    {
        $userSession = new UserSession();
        $userSession->user_id = 'test';
        $userSession->role = 'user';
        $userSession->email = "test@user.com";
        $encodeSession = $this->sessionRepository->getToken($userSession);

        self::assertInstanceOf(EncodedSession::class, $encodeSession);
    }

    public function testDecodeToken()
    {
        $userSession = new UserSession();
        $userSession->user_id = 'test';
        $userSession->role = 'user';
        $userSession->email = "user@mail.com";
        $encodeSession = $this->sessionRepository->getToken($userSession);

        $decode = new Decode();
        $decode->token = $encodeSession->key;
        $decoded = $this->sessionRepository->decodeToken($decode);

        self::assertInstanceOf(DecodedSession::class, $decoded);
        self::assertEquals('test', $decoded->payload->data->user_id);
        self::assertEquals('user', $decoded->payload->data->role);
        self::assertEquals('user@mail.com', $decoded->payload->data->email);
    }

}

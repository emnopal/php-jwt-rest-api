<?php

namespace BadHabit\LoginManagement\Service;

require_once __DIR__ . '/../Helper/helper.php';

use BadHabit\LoginManagement\Auth\Handler;
use BadHabit\LoginManagement\Config\Database;
use BadHabit\LoginManagement\Domain\UserSession;
use BadHabit\LoginManagement\Model\DecodeSession;
use BadHabit\LoginManagement\Domain\Decode;
use BadHabit\LoginManagement\Domain\User;
use BadHabit\LoginManagement\Repository\SessionRepository;
use BadHabit\LoginManagement\Repository\UserRepository;
use PHPUnit\Framework\TestCase;

class SessionServiceTest extends TestCase
{
    private SessionService $sessionService;
    private SessionRepository $sessionRepository;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->sessionRepository = new SessionRepository(new Handler());
        $this->userRepository = new UserRepository(Database::getConnection());
        $this->sessionService = new SessionService($this->sessionRepository, $this->userRepository);

        $this->sessionService->destroy();
        $this->userRepository->deleteAll();
    }

    public function testCreateSession(): void
    {
        $user = new User();
        $user->username = "test";
        $user->full_name = "test Account";
        $user->password = "test123";
        $user->email = "user@mail.com";
        $user->role = "user";
        $this->userRepository->save($user);

        $userSession = new UserSession();
        $userSession->user_id = $user->username;
        $userSession->role = $user->role;
        $userSession->email = $user->email;

        $this->sessionService->create($userSession);
        $token = $this->sessionRepository->getToken($userSession);
        $key = $token->key;

        $this->expectOutputRegex("[X-BHB-SESSION: $key]");

        $decode = new Decode();
        $decode->token = $key;
        $decodedToken = $this->sessionRepository->decodeToken($decode);

        self::assertEquals("test", $decodedToken->payload->data->user_id);
        self::assertEquals("user", $decodedToken->payload->data->role);
        self::assertEquals("user@mail.com", $decodedToken->payload->data->email);
    }

    public function testCreateSessionAdmin(): void
    {
        $user = new User();
        $user->username = "test";
        $user->full_name = "test Account";
        $user->password = "test123";
        $user->email = "user@mail.com";
        $user->role = "admin";
        $this->userRepository->save($user);

        $userSession = new UserSession();
        $userSession->user_id = $user->username;
        $userSession->role = $user->role;
        $userSession->email = $user->email;

        $this->sessionService->create($userSession);
        $token = $this->sessionRepository->getToken($userSession);
        $key = $token->key;

        $this->expectOutputRegex("[X-BHB-SESSION: $key]");

        $decode = new Decode();
        $decode->token = $key;
        $decodedToken = $this->sessionRepository->decodeToken($decode);

        self::assertEquals("test", $decodedToken->payload->data->user_id);
        self::assertEquals("admin", $decodedToken->payload->data->role);
        self::assertEquals("user@mail.com", $decodedToken->payload->data->email);
    }

    public function testDestroySession(): void
    {
        $user = new User();
        $user->username = "test";
        $user->full_name = "test Account";
        $user->password = "test123";
        $user->email = "user@mail.com";
        $user->role = "user";
        $this->userRepository->save($user);

        $userSession = new UserSession();
        $userSession->user_id = $user->username;
        $userSession->role = $user->role;
        $userSession->email = $user->email;

        $this->sessionService->create($userSession);
        $token = $this->sessionRepository->getToken($userSession);
        $key = $token->key;

        $_COOKIE[SessionService::$COOKIE_NAME] = $key;
        $this->sessionService->destroy();
        $this->expectOutputRegex("[X-BHB-SESSION: ]");
    }

    public function testDestroySessionAdmin(): void
    {
        $user = new User();
        $user->username = "test";
        $user->full_name = "test Account";
        $user->password = "test123";
        $user->email = "user@mail.com";
        $user->role = "admin";
        $this->userRepository->save($user);

        $userSession = new UserSession();
        $userSession->user_id = $user->username;
        $userSession->role = $user->role;
        $userSession->email = $user->email;

        $this->sessionService->create($userSession);
        $token = $this->sessionRepository->getToken($userSession);
        $key = $token->key;

        $_COOKIE[SessionService::$COOKIE_NAME] = $key;
        $this->sessionService->destroy();
        $this->expectOutputRegex("[X-BHB-SESSION: ]");
    }

    public function testCurrentSession(): void
    {
        $user = new User();
        $user->username = "test";
        $user->full_name = "test Account";
        $user->password = "test123";
        $user->email = "user@mail.com";
        $user->role = "user";
        $this->userRepository->save($user);

        $userSession = new UserSession();
        $userSession->user_id = $user->username;
        $userSession->role = $user->role;
        $userSession->email = $user->email;

        $this->sessionService->create($userSession);
        $token = $this->sessionRepository->getToken($userSession);
        $key = $token->key;

        $_COOKIE[SessionService::$COOKIE_NAME] = $key;
        $user = $this->sessionService->current();
        self::assertEquals("test", $user->username);
        self::assertEquals("user", $user->role);
        self::assertEquals("user@mail.com", $user->email);
    }

    public function testCurrentSessionAdmin(): void
    {
        $user = new User();
        $user->username = "test";
        $user->full_name = "test Account";
        $user->password = "test123";
        $user->email = "user@mail.com";
        $user->role = "admin";
        $this->userRepository->save($user);

        $userSession = new UserSession();
        $userSession->user_id = $user->username;
        $userSession->role = $user->role;
        $userSession->email = $user->email;

        $this->sessionService->create($userSession);
        $token = $this->sessionRepository->getToken($userSession);
        $key = $token->key;

        $_COOKIE[SessionService::$COOKIE_NAME] = $key;
        $user = $this->sessionService->current();
        self::assertEquals("test", $user->username);
        self::assertEquals("admin", $user->role);
        self::assertEquals("user@mail.com", $user->email);
    }

}

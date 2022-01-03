<?php

namespace BadHabit\LoginManagement\Middleware;

require_once __DIR__ . "/../Helper/helper.php";

use BadHabit\LoginManagement\Auth\Handler;
use BadHabit\LoginManagement\Config\Database;
use BadHabit\LoginManagement\Domain\User;
use BadHabit\LoginManagement\Domain\UserSession;
use BadHabit\LoginManagement\Repository\SessionRepository;
use BadHabit\LoginManagement\Repository\UserRepository;
use BadHabit\LoginManagement\Service\SessionService;
use PHPUnit\Framework\TestCase;

class MustNotLoginMiddlewareTest extends TestCase
{


    private MustNotLoginMiddleware $mustNotLoginMiddleware;
    private UserRepository $userRepository;
    private SessionRepository $sessionRepository;
    private SessionService $sessionService;

    public function setUp(): void
    {
        $this->mustNotLoginMiddleware = new MustNotLoginMiddleware();
        putenv("mode=test");

        $this->userRepository = new UserRepository(Database::getConnection());
        $this->sessionRepository = new SessionRepository(new Handler());
        $this->sessionService = new SessionService($this->sessionRepository, $this->userRepository);

        $this->sessionService->destroy();
        $this->userRepository->deleteAll();

    }

    public function testBeforeLogin()
    {
        $this->mustNotLoginMiddleware->before();
        $this->expectOutputRegex("[X-BHB-SESSION: ]");

    }

    public function testBeforeLoggedIn()
    {
        $user = new User();
        $user->username = "test";
        $user->full_name = "test";
        $user->password = "test";
        $user->email = "user@test.com";
        $user->role = "user";

        $this->userRepository->save($user);

        $userSession = new UserSession();
        $userSession->user_id = $user->username;
        $userSession->role = $user->role;
        $userSession->email = $user->email;

        $this->sessionService->create($userSession);
        $token = $this->sessionRepository->getToken($userSession);
        $_COOKIE[SessionService::$COOKIE_NAME] = $token->key;

        $this->mustNotLoginMiddleware->before();
        $this->expectOutputRegex("[Location: ]");
    }

    public function testBeforeLoggedInAdmin()
    {
        $user = new User();
        $user->username = "test";
        $user->full_name = "test";
        $user->password = "test";
        $user->email = "user@test.com";
        $user->role = "admin";

        $this->userRepository->save($user);

        $userSession = new UserSession();
        $userSession->user_id = $user->username;
        $userSession->role = $user->role;
        $userSession->email = $user->email;

        $this->sessionService->create($userSession);
        $token = $this->sessionRepository->getToken($userSession);
        $_COOKIE[SessionService::$COOKIE_NAME] = $token->key;

        $this->mustNotLoginMiddleware->before();
        $this->expectOutputRegex("[Location: ]");

    }
}




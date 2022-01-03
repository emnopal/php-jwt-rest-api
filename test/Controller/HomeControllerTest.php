<?php

namespace BadHabit\LoginManagement\Controller;

require_once __DIR__ . "/../Helper/helper.php";

use BadHabit\LoginManagement\Auth\Handler;
use BadHabit\LoginManagement\Config\Database;
use BadHabit\LoginManagement\Domain\User;
use BadHabit\LoginManagement\Domain\UserSession;
use BadHabit\LoginManagement\Repository\SessionRepository;
use BadHabit\LoginManagement\Repository\UserRepository;
use BadHabit\LoginManagement\Service\SessionService;
use PHPUnit\Framework\TestCase;

class HomeControllerTest extends TestCase
{


    private HomeController $homeController;
    private UserRepository $userRepository;
    private SessionRepository $sessionRepository;
    private SessionService $sessionService;

    protected function setUp(): void
    {
        $this->homeController = new HomeController();

        $this->userRepository = new UserRepository(Database::getConnection());
        $this->sessionRepository = new SessionRepository(new Handler(), 'test');
        $this->sessionService = new SessionService($this->sessionRepository, $this->userRepository);

        $this->sessionService->destroy();
        $this->userRepository->deleteAll();
    }

    public function testGuess(): void
    {
        $this->homeController->index();
        $this->expectOutputRegex("[Login Management]");
    }

    public function testUserLogin(): void
    {
        $user = new User();
        $user->username = "test";
        $user->password = "test";
        $user->full_name = "test User";
        $user->email = "test@gmail.com";
        $user->role = "user";

        $this->userRepository->save($user);

        $userSession = new UserSession();
        $userSession->user_id = $user->username;
        $userSession->role = $user->role;
        $userSession->email = $user->email;

        $this->sessionService->create($userSession);
        $token = $this->sessionRepository->getToken($userSession);
        $_COOKIE[SessionService::$COOKIE_NAME] = $token->key;

        $this->homeController->index();
        $this->expectOutputRegex("[Hello, <br>" . ucwords($user->full_name) . "]");
    }


}

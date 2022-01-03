<?php

namespace BadHabit\LoginManagement\Controller;

require_once __DIR__ . "/../Helper/helper.php";

use BadHabit\LoginManagement\Auth\Handler;
use BadHabit\LoginManagement\Config\Database;
use BadHabit\LoginManagement\Domain\Decode;
use BadHabit\LoginManagement\Domain\User;
use BadHabit\LoginManagement\Domain\UserSession;
use BadHabit\LoginManagement\Repository\SessionRepository;
use BadHabit\LoginManagement\Repository\UserRepository;
use BadHabit\LoginManagement\Service\SessionService;
use PHPUnit\Framework\TestCase;

class AdminControllerTest extends TestCase
{

    private UserController $userController;
    private UserRepository $userRepository;
    private SessionRepository $sessionRepository;
    private SessionService $sessionService;
    private AdminController $adminController;

    protected function setUp(): void
    {
        $this->userController = new UserController();
        $this->adminController = new AdminController();
        $this->userRepository = new UserRepository(Database::getConnection());

        $this->sessionRepository = new SessionRepository(new Handler());
        $this->sessionService = new SessionService($this->sessionRepository, $this->userRepository);
        $this->sessionService->destroy();

        $this->userRepository->deleteAll();

        // Change the env mode to test
        putenv('mode=test');
    }

    public function testLoginAdminSuccess()
    {
        $user = new User();
        $user->username = "akuntest";
        $user->full_name = "test User";
        $user->password = password_hash("test123", PASSWORD_BCRYPT);
        $user->email = "user@mail.com";

        $user->role = "admin";
        $this->userRepository->save($user);

        $userSession = new UserSession();
        $userSession->user_id = $user->username;
        $userSession->role = $user->role;
        $userSession->email = $user->email;

        $this->sessionService->create($userSession);
        $token = $this->sessionRepository->getToken($userSession);
        $_COOKIE[SessionService::$COOKIE_NAME] = $token->key;

        // This will redirect
        // but redirect is giving a problem with PHPUnit test
        // So we need to create a new function to create mock header
        $this->adminController->index();
        $this->expectOutputRegex("[X-BHB-SESSION: ]");
        $this->expectOutputRegex("[Welcome Admin]");
        $this->expectOutputRegex("[Admin]");
    }

    public function testLoginAdminSuccessToUser()
    {

        $user = new User();
        $user->username = "akuntest";
        $user->full_name = "test User";
        $user->password = password_hash("test123", PASSWORD_BCRYPT);
        $user->email = "user@mail.com";
        $user->role = "admin";

        $this->userRepository->save($user);

        $_POST['username'] = "akuntest";
        $_POST['password'] = "test123";

        // This will redirect
        // but redirect is giving a problem with PHPUnit test
        // So we need to create a new function to create mock header
        $this->userController->postLogin();
        $this->expectOutputRegex("[X-BHB-SESSION: ]");
        $this->expectOutputRegex("[Location: /admin]");
    }

    public function testLoginAdminFailedBecauseUser()
    {
        $user = new User();
        $user->username = "akuntest";
        $user->full_name = "test User";
        $user->password = password_hash("test123", PASSWORD_BCRYPT);
        $user->email = "user@mail.com";

        $user->role = "user";
        $this->userRepository->save($user);

        $userSession = new UserSession();
        $userSession->user_id = $user->username;
        $userSession->role = $user->role;
        $userSession->email = $user->email;

        $this->sessionService->create($userSession);
        $token = $this->sessionRepository->getToken($userSession);
        $_COOKIE[SessionService::$COOKIE_NAME] = $token->key;

        // This will redirect
        // but redirect is giving a problem with PHPUnit test
        // So we need to create a new function to create mock header
        $this->adminController->index();
        $this->expectOutputRegex("[X-BHB-SESSION: ]");
        $this->expectOutputRegex("[404]");
        $this->expectOutputRegex("[The page you're trying to access is not found :(]");
        $this->expectOutputRegex("[http://localhost/admin]");
    }

}


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

class UserControllerTest extends TestCase
{

    private UserController $userController;
    private UserRepository $userRepository;
    private SessionRepository $sessionRepository;
    private SessionService $sessionService;

    protected function setUp(): void
    {
        $this->userController = new UserController();
        $this->userRepository = new UserRepository(Database::getConnection());

        $this->sessionRepository = new SessionRepository(new Handler());
        $this->sessionService = new SessionService($this->sessionRepository, $this->userRepository);
        $this->sessionService->destroy();

        $this->userRepository->deleteAll();

        // Change the env mode to test
        putenv('mode=test');
    }

    public function testRegister()
    {

        // Only render html view
        $this->userController->register();
        $this->expectOutputRegex("[Register]");
        $this->expectOutputRegex("[Username]");
        $this->expectOutputRegex("[Full Name]");
        $this->expectOutputRegex("[Password]");
        $this->expectOutputRegex("[Email]");

    }

    public function testPostRegisterSuccess()
    {
        $_POST['username'] = "test1234";
        $_POST['fullName'] = "test4568";
        $_POST['password'] = "test7890";
        $_POST['email'] = "test@mail.com";

        $user = $_POST['username'];

        // This is will redirect
        // but redirect is giving a problem with PHPUnit test
        // So we need to create a new function to create mock header
        $this->userController->postRegister();
        $this->expectOutputRegex("[User registered successfully]");
        $this->expectOutputRegex("[$user]");
    }

    public function testPostRegisterFailed()
    {
        $_POST['username'] = "";
        $_POST['password'] = "";
        $_POST['fullName'] = "";
        $_POST['email'] = "";

        $this->userController->postRegister();

        $this->expectOutputRegex("[Invalid Username]");
    }

    public function testPostRegisterDuplicate()
    {
        $user = new User();
        $user->username = "akuntest";
        $user->full_name = "test User";
        $user->password = "test1234";
        $user->email = "test@mail.com";

        $this->userRepository->save($user);

        $_POST['username'] = "akuntest";
        $_POST['fullName'] = "test User";
        $_POST['password'] = "test1234";
        $_POST['email'] = "test@mail.com";

        $this->userController->postRegister();

        $this->expectOutputRegex("[User already exists]");
    }


    public function testLogin()
    {
        // Only render html view
        $this->userController->login();
        $this->expectOutputRegex("[Login]");
        $this->expectOutputRegex("[Username]");
        $this->expectOutputRegex("[Password]");
    }

    public function testLoginSuccess()
    {

        $user = new User();
        $user->username = "akuntest";
        $user->full_name = "test User";
        $user->password = password_hash("test123", PASSWORD_BCRYPT);
        $user->email = "user@mail.com";

        $this->userRepository->save($user);

        $_POST['username'] = "akuntest";
        $_POST['password'] = "test123";

        // This is will redirect
        // but redirect is giving a problem with PHPUnit test
        // So we need to create a new function to create mock header
        $this->userController->postLogin();
        $this->expectOutputRegex("[X-BHB-SESSION: ]");
        $this->expectOutputRegex("[Location: /]");
    }

    public function testLoginValidationError()
    {
        $_POST['username'] = "";
        $_POST['fullName'] = "";
        $_POST['password'] = "";
        $_POST['email'] = "";

        $this->userController->postLogin();

        $this->expectOutputRegex("[Username or Password not Valid]");
    }

    public function testLoginUserNotFound()
    {
        $user = new User();
        $user->username = "akuntest";
        $user->full_name = "test User";
        $user->password = password_hash("test123", PASSWORD_BCRYPT);
        $user->email = "user@mail.com";

        $this->userRepository->save($user);

        $_POST['username'] = "akuntest123";
        $_POST['password'] = "test123";

        $this->userController->postLogin();

        $this->expectOutputRegex("[Username or Password is wrong]");
    }

    public function testLoginWrongPassword()
    {
        $user = new User();
        $user->username = "akuntest";
        $user->full_name = "test User";
        $user->password = password_hash("test123", PASSWORD_BCRYPT);
        $user->email = "user@mail.com";

        $this->userRepository->save($user);

        $_POST['username'] = "akuntest";
        $_POST['password'] = "test123456";

        $this->userController->postLogin();

        $this->expectOutputRegex("[Username or Password is wrong]");
    }

    public function testLogout()
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

        $this->userController->logout();
        $this->expectOutputRegex("[X-BHB-SESSION: ]");
        $this->expectOutputRegex("[Location: /]");

    }

    public function testUpdateProfile()
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

        $this->userController->updateProfile();

        $this->expectOutputRegex("[Update Profile]");
        $this->expectOutputRegex("[Profile]");
        $this->expectOutputRegex("[$user->full_name]");
        $this->expectOutputRegex("[$user->username]");
        $this->expectOutputRegex("[$user->email]");
    }

    public function testUpdateProfileSuccess()
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

        $_POST['fullName'] = "test User Update";
        $_POST['email'] = "mail@test.com";
        $this->userController->postUpdateProfile();

        $this->expectOutputRegex("[Location: /]");

        $result = $this->userRepository->findById("akuntest");
        self::assertEquals("test User Update", $result->full_name);
        self::assertEquals("mail@test.com", $result->email);
    }

    public function testUpdateUserFailed()
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

        $_POST['fullName'] = "";
        $_POST['email'] = "";
        $this->userController->postUpdateProfile();

        $this->expectOutputRegex("[Invalid name]");
    }

    public function testUpdatePassword()
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

        $this->userController->updatePassword();

        $this->expectOutputRegex("[Password]");
        $this->expectOutputRegex("[Change Password]");
        $this->expectOutputRegex("[Old Password]");
        $this->expectOutputRegex("[New Password]");
        $this->expectOutputRegex("[$user->username]");
    }

    public function testUpdatePasswordSuccess()
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

        $_POST['old'] = "test123";
        $_POST['new'] = "test";
        $this->userController->postUpdatePassword();

        $this->expectOutputRegex("[Location: /]");

        $result = $this->userRepository->findById("akuntest");
        self::assertTrue(password_verify($_POST['new'], $result->password));
    }

    public function testUpdatePasswordFailedNotMatchOldPassword()
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

        $_POST['old'] = "test";
        $_POST['new'] = "test456";
        $this->userController->postUpdatePassword();

        $this->expectOutputRegex("[Old password is not match]");
    }

    public function testUpdatePasswordFailed()
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

        $_POST['old'] = "test123";
        $_POST['new'] = "";
        $this->userController->postUpdatePassword();

        $this->expectOutputRegex("[Invalid username or password]");
    }

}

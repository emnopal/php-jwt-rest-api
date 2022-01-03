<?php

namespace BadHabit\LoginManagement\Service;

require_once __DIR__ . "/../Helper/helper.php";

use BadHabit\LoginManagement\Config\Database;
use BadHabit\LoginManagement\Domain\User;
use BadHabit\LoginManagement\Exception\ValidationException;
use BadHabit\LoginManagement\Model\UserLoginRequest;
use BadHabit\LoginManagement\Model\UserPasswordRequest;
use BadHabit\LoginManagement\Model\UserProfileUpdateRequest;
use BadHabit\LoginManagement\Model\UserRegisterRequest;
use BadHabit\LoginManagement\Repository\UserRepository;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{


    private UserService $userService;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $connection = Database::getConnection();
        $this->userRepository = new UserRepository($connection);
        $this->userService = new UserService($this->userRepository);
        $this->userRepository->deleteAll();
    }

    public function testRegisterSuccess()
    {

        $request = new UserRegisterRequest();
        $request->username = "akuntest";
        $request->fullName = "test User";
        $request->password = "test123";
        $request->email = "user@mail.com";

        $response = $this->userService->register($request);

        self::assertEquals($request->username, $response->user->username);
        self::assertEquals($request->fullName, $response->user->full_name);
        self::assertEquals($request->email, $response->user->email);
        self::assertNotEquals($request->password, $response->user->password);

        self::assertTrue(password_verify($request->password, $response->user->password));
    }

    public function testRegisterFailed()
    {
        $this->expectException(ValidationException::class);
        $request = new UserRegisterRequest();
        $request->username = "";
        $request->fullName = "";
        $request->password = "";
        $request->email = "";

        $response = $this->userService->register($request);
    }

    public function testRegisterDuplicate()
    {
        $user = new User();
        $user->username = "akuntest";
        $user->full_name = "test User";
        $user->password = "test123";
        $user->email = "user@mail.com";

        $this->userRepository->save($user);

        $this->expectException(ValidationException::class);

        $request = new UserRegisterRequest();
        $request->username = "akuntest";
        $request->fullName = "test User";
        $request->password = "test123";
        $request->email = "user@mail.com";

        $response = $this->userService->register($request);

    }

    public function testLoginNotFound()
    {
        $this->expectException(ValidationException::class);

        $request = new UserLoginRequest();
        $request->username = "akuntest";
        $request->password = "rahasia";

        $this->userService->login($request);
    }

    public function testLoginWrongPassword()
    {
        $user = new User();
        $user->username = "akuntest";
        $user->full_name = "test User";
        $user->email = "user@mail.com";
        $user->password = password_hash("test123", PASSWORD_BCRYPT);

        $this->expectException(ValidationException::class);

        $request = new UserLoginRequest();
        $request->username = "akuntest";
        $request->password = "rahasia";

        $this->userService->login($request);
    }

    public function testLoginSuccess()
    {
        $user = new User();
        $user->username = "akuntest";
        $user->full_name = "test User";
        $user->email = "user@mail.com";
        $user->password = password_hash("test123", PASSWORD_BCRYPT);

        $this->expectException(ValidationException::class);

        $request = new UserLoginRequest();
        $request->username = "akuntest";
        $request->password = "test123";

        $response = $this->userService->login($request);
        self::assertEquals($request->username, $response->user->username);
        self::assertTrue(password_verify($request->password, $response->user->password));

    }

    public function testUpdateProfileSuccess()
    {
        $user = new User();
        $user->username = "akuntest";
        $user->full_name = "test User";
        $user->email = "user@mail.com";
        $user->password = password_hash("test123", PASSWORD_BCRYPT);

        $this->userRepository->save($user);

        $request = new UserProfileUpdateRequest();
        $request->username = "akuntest";
        $request->fullName = "test New User";
        $request->email = "user2@mail.com";

        $this->userService->updateProfile($request);

        $result = $this->userRepository->findById($request->username);

        self::assertEquals($request->fullName, $result->full_name);
        self::assertEquals($request->email, $result->email);
    }

    public function testUpdateProfileFailed()
    {
        $this->expectException(ValidationException::class);

        $request = new UserProfileUpdateRequest();
        $request->username = "akuntest";
        $request->fullName = "";
        $request->email = "";

        $this->userService->updateProfile($request);

    }

    public function testUpdateProfileNotFound()
    {
        $this->expectException(ValidationException::class);

        $request = new UserProfileUpdateRequest();
        $request->username = "akuntest2";
        $request->fullName = "akusayangkamu";
        $request->email = "uuu@uid.com";

        $this->userService->updateProfile($request);
    }

    public function testUpdatePasswordSuccess()
    {

        $user = new User();
        $user->username = "akuntest";
        $user->full_name = "test User";
        $user->email = "user@mail.com";
        $user->password = password_hash("test123", PASSWORD_BCRYPT);

        $this->userRepository->save($user);

        $request = new UserPasswordRequest();
        $request->username = "akuntest";
        $request->old = "test123";
        $request->new = "testaja";

        $this->userService->updatePassword($request);

        $result = $this->userRepository->findById($request->username);

        self::assertTrue(password_verify($request->new, $result->password));

    }

    public function testUpdatePasswordFailed()
    {
        $this->expectException(ValidationException::class);

        $user = new User();
        $user->username = "akuntest";
        $user->full_name = "test User";
        $user->email = "user@mail.com";
        $user->password = password_hash("test123", PASSWORD_BCRYPT);

        $this->userRepository->save($user);

        $request = new UserPasswordRequest();
        $request->username = "akuntest";
        $request->old = "test123";
        $request->new = "";

        $this->userService->updatePassword($request);
    }

    public function testUpdatePasswordUserNotFound()
    {
        $this->expectException(ValidationException::class);

        $user = new User();
        $user->username = "akuntest";
        $user->full_name = "test User";
        $user->email = "user@mail.com";
        $user->password = password_hash("test123", PASSWORD_BCRYPT);

        $this->userRepository->save($user);

        $request = new UserPasswordRequest();
        $request->username = "akuntestaja";
        $request->old = "test123";
        $request->new = "testaja";

        $this->userService->updatePassword($request);
    }

    public function testUpdatePasswordOldPasswordNotMatch()
    {
        $this->expectException(ValidationException::class);

        $user = new User();
        $user->username = "akuntest";
        $user->full_name = "test User";
        $user->email = "user@mail.com";
        $user->password = password_hash("test123", PASSWORD_BCRYPT);

        $this->userRepository->save($user);

        $request = new UserPasswordRequest();
        $request->username = "akuntestaja";
        $request->old = "test123456";
        $request->new = "testaja";

        $this->userService->updatePassword($request);
    }


}

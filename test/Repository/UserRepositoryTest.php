<?php

namespace BadHabit\LoginManagement\Repository;

use BadHabit\LoginManagement\Domain\User;
use BadHabit\LoginManagement\Config\Database;
use PHPUnit\Framework\TestCase;

class UserRepositoryTest extends TestCase
{
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->userRepository = new UserRepository(Database::getConnection());
        $this->userRepository->deleteAll();
    }

    public function testSaveSuccess()
    {
        $user = new User();
        $user->full_name = 'test';
        $user->password = 'test';
        $user->username = 'u001';
        $user->email = "user@mail.com";

        $this->userRepository->save($user);
        $result = $this->userRepository->findById($user->username);

        self::assertEquals($user->username, $result->username);
        self::assertEquals($user->full_name, $result->full_name);
        self::assertEquals($user->password, $result->password);
        self::assertEquals($user->email, $result->email);
    }

    public function testFindByIdNotFound()
    {
        $result = $this->userRepository->findById("testnotfound");
        self::assertNull($result);
    }

    public function testUpdate()
    {

        $user = new User();
        $user->full_name = 'test';
        $user->password = 'test';
        $user->username = 'u001';
        $user->email = "user@mail.com";

        $this->userRepository->save($user);

        $user->full_name = 'test2';
        $user->email = "user2@mail.com";

        $this->userRepository->update($user);

        $result = $this->userRepository->findById($user->username);

        self::assertEquals($user->username, $result->username);
        self::assertEquals($user->full_name, $result->full_name);
        self::assertEquals($user->password, $result->password);
        self::assertEquals($user->email, $result->email);
    }
}

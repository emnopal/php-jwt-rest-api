<?php

namespace BadHabit\LoginManagement\Service;

use BadHabit\LoginManagement\Config\Database;
use BadHabit\LoginManagement\Exception\ValidationException;
use BadHabit\LoginManagement\Model\UserLoginRequest;
use BadHabit\LoginManagement\Model\UserLoginResponse;
use BadHabit\LoginManagement\Model\UserPasswordRequest;
use BadHabit\LoginManagement\Model\UserPasswordResponse;
use BadHabit\LoginManagement\Model\UserProfileUpdateIDRequest;
use BadHabit\LoginManagement\Model\UserProfileUpdateIDResponse;
use BadHabit\LoginManagement\Model\UserProfileUpdateRequest;
use BadHabit\LoginManagement\Model\UserProfileUpdateResponse;
use BadHabit\LoginManagement\Model\UserRegisterRequest;
use BadHabit\LoginManagement\Model\UserRegisterResponse;
use BadHabit\LoginManagement\Repository\UserRepository;
use BadHabit\LoginManagement\Domain\User;
use PHPUnit\Util\Exception;

class UserService
{

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(UserRegisterRequest $request): UserRegisterResponse
    {
        try {
            Database::beginTransaction();
            $this->validateUserRegistrationRequest($request);
            $user = $this->userRepository->findById($request->username);
            if ($user != null) {
                throw new ValidationException('User already exists');
            }
            $user = new User();
            $user->username = $request->username;
            $user->password = password_hash($request->password, PASSWORD_BCRYPT);
            $user->full_name = $request->fullName;
            $user->email = $request->email;
            $this->userRepository->save($user);
            $response = new UserRegisterResponse();
            $response->user = $user;
            Database::commitTransaction();
            return $response;
        } catch (\Exception $e) {
            Database::rollbackTransaction();
            throw $e;
        }
    }

    private function validateUserRegistrationRequest(UserRegisterRequest $request)
    {
        if (
            $request->username == null ||
            trim($request->username) == ''
        ) {
            throw new ValidationException('Invalid Username');
        } else if (
            $request->email == null ||
            trim($request->email) == '' ||
            !filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Invalid email');
        } else if (
            $request->fullName == null || trim($request->fullName) == ''
        ) {
            throw new ValidationException('Invalid username');
        } else if (
            $request->password == null || trim($request->password) == ''
        ) {
            throw new ValidationException('Invalid password');
        }
    }

    public function login(UserLoginRequest $request): UserLoginResponse
    {
        $this->validateUserLoginRequest($request);
        $user = $this->userRepository->findById($request->username);
        if (!$user) {
            throw new ValidationException('Username or Password is wrong');
        }

        if (password_verify($request->password, $user->password)) {
            $response = new UserLoginResponse();
            $response->user = $user;
            return $response;
        } else {
            throw new ValidationException('Username or Password is wrong');
        }
    }

    private function validateUserLoginRequest(UserLoginRequest $request)
    {
        if (
            $request->username == null || $request->password == null ||
            trim($request->username) == '' || trim($request->password) == ''
        ) {
            throw new ValidationException('Username or Password not Valid');
        }
    }

    public function updateProfile(UserProfileUpdateRequest $request): UserProfileUpdateResponse
    {
        try {
            Database::beginTransaction();
            $this->validateUserProfileUpdateRequest($request);
            $user = $this->userRepository->findById($request->username);
            if (!$user) {
                throw new ValidationException('User is not found');
            }
            $user->full_name = $request->fullName;
            $user->email = $request->email;
            $this->userRepository->update($user);
            Database::commitTransaction();
            $response = new UserProfileUpdateResponse();
            $response->user = $user;
            return $response;
        } catch (\Exception| \PDOException $e) {
            Database::rollbackTransaction();
            throw new Exception("email already exists");
        }
    }

    private function validateUserProfileUpdateRequest(UserProfileUpdateRequest $request)
    {
        if (
            $request->username == null || trim($request->username) == ''
        ) {
            throw new ValidationException('Invalid username');
        } elseif ($request->fullName == null || trim($request->fullName) == '') {
            throw new ValidationException('Invalid name');
        } elseif ($request->email == null || trim($request->email) == '' ||
            !filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Invalid email');
        }
    }

    public function updateID(UserProfileUpdateIDRequest $request): UserProfileUpdateIDResponse
    {
        try {
            Database::beginTransaction();
            $this->validateUserProfileIDUpdateRequest($request);
            $user = $this->userRepository->findById($request->old_username);
            if (!$user) {
                throw new ValidationException('User is not found');
            }
            $this->userRepository->changeId($request->old_username, $request->new_username);
            $user = $this->userRepository->findById($request->new_username);
            Database::commitTransaction();
            $response = new UserProfileUpdateIDResponse();
            $response->user = $user;
            return $response;
        } catch (\Exception $e) {
            Database::rollbackTransaction();
            throw new Exception("username already exists");
        }
    }

    private function validateUserProfileIDUpdateRequest(UserProfileUpdateIDRequest $request)
    {
        if (
            $request->old_username == null || trim($request->old_username) == ''
        ) {
            throw new ValidationException('Invalid username');
        } elseif (
            $request->new_username == null || trim($request->new_username) == ''
        ) {
            throw new ValidationException('Invalid new username');
        }
    }

    public function updatePassword(UserPasswordRequest $request): UserPasswordResponse
    {
        try {
            Database::beginTransaction();
            $this->validateUserPasswordUpdateRequest($request);
            $user = $this->userRepository->findById($request->username);
            if (!$user) {
                throw new ValidationException('User is not found');
            }
            if (!password_verify($request->old, $user->password)) {
                throw new ValidationException('Old password is not match');
            }
            $user->password = password_hash($request->new, PASSWORD_BCRYPT);
            $this->userRepository->update($user);
            Database::commitTransaction();
            $response = new UserPasswordResponse();
            $response->user = $user;
            return $response;
        } catch (\Exception $e) {
            Database::rollbackTransaction();
            throw $e;
        }
    }

    private function validateUserPasswordUpdateRequest(UserPasswordRequest $request)
    {
        if (
            $request->username == null || trim($request->username) == '' ||
            $request->old == null || trim($request->old) == '' ||
            $request->new == null || trim($request->new) == ''
        ) {
            throw new ValidationException('Invalid username or password');
        }
    }

}
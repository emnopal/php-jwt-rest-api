<?php

namespace BadHabit\LoginManagement\Controller;

use BadHabit\LoginManagement\Auth\Handler;
use BadHabit\LoginManagement\App\View;
use BadHabit\LoginManagement\Config\Database;
use BadHabit\LoginManagement\Helper\ControllerMessage;
use BadHabit\LoginManagement\Repository\SessionRepository;
use BadHabit\LoginManagement\Repository\UserRepository;
use BadHabit\LoginManagement\Service\SessionService;

class AdminController
{
    private SessionService $sessionService;

    public function __construct()
    {
        $userRepository = new UserRepository(Database::getConnection());

        $auth = new Handler();
        $sessionRepository = new SessionRepository($auth);
        $this->sessionService = new SessionService($sessionRepository, $userRepository);
    }

    public function index(): void
    {
        try {
            ControllerMessage::message(true, 200, 'Welcome to the admin panel');
        } catch (\Exception $e) {
            ControllerMessage::message(false, 500, $e->getMessage());
        }
    }
}
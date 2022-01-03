<?php

namespace BadHabit\LoginManagement\Middleware;

use BadHabit\LoginManagement\Auth\Handler;
use BadHabit\LoginManagement\App\View;
use BadHabit\LoginManagement\Config\Database;
use BadHabit\LoginManagement\Repository\SessionRepository;
use BadHabit\LoginManagement\Repository\UserRepository;
use BadHabit\LoginManagement\Service\SessionService;

class MustNotLoginMiddleware implements Middleware
{

    private SessionService $sessionService;

    public function __construct()
    {
        $userRepository = new UserRepository(Database::getConnection());

        $auth = new Handler();
        $sessionRepository = new SessionRepository($auth);
        $this->sessionService = new SessionService($sessionRepository, $userRepository);
    }

    public function before(): void
    {
        try{
            $this->sessionService->current();
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                echo json_encode([
                    'success'=>false,
                    'status' => 401,
                    'message' => 'You are already logged in'
                ]);
                exit;
            }
        } catch (\Exception $e) {
            return;
        }
    }


}
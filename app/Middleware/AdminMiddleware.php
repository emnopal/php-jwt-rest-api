<?php

namespace BadHabit\LoginManagement\Middleware;

use BadHabit\LoginManagement\App\Router;
use BadHabit\LoginManagement\Auth\Handler;
use BadHabit\LoginManagement\Config\Database;
use BadHabit\LoginManagement\Helper\ControllerMessage;
use BadHabit\LoginManagement\Repository\SessionRepository;
use BadHabit\LoginManagement\Repository\UserRepository;
use BadHabit\LoginManagement\Service\SessionService;

class AdminMiddleware
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
        try {
            $user = $this->sessionService->currentAdmin();
            if (!isset(Router::getHeader()['Authorization'])) {
                throw new \Exception('No token provided');
            }
            if ($key = Router::getHeader()['Authorization']) {
                list(, $token) = explode(' ', $key);
                $this->sessionService->authHeaders($token);
            }
        } catch (\Exception $e) {
            ControllerMessage::message(false, 401, "Unauthorized: " . $e->getMessage());
        }
    }
}
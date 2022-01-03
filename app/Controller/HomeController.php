<?php

namespace BadHabit\LoginManagement\Controller;

use BadHabit\LoginManagement\Auth\Handler;
use BadHabit\LoginManagement\Config\Database;
use BadHabit\LoginManagement\Helper\ControllerMessage;
use BadHabit\LoginManagement\Repository\SessionRepository;
use BadHabit\LoginManagement\Repository\UserRepository;
use BadHabit\LoginManagement\Service\SessionService;

class HomeController
{


    private SessionService $sessionService;

    public function __construct()
    {
        $auth = new Handler();
        $sessionRepository = new SessionRepository($auth);
        $userRepository = new UserRepository(Database::getConnection());
        $this->sessionService = new SessionService($sessionRepository, $userRepository);
    }

    function index()
    {
        try{
            $user = $this->sessionService->current();
            ControllerMessage::message(true, 200, 'Welcome to the home page ' .$user->full_name);
        } catch (\Exception $e){
            ControllerMessage::message(true, 200, 'Welcome to the home page, register or log in to explore more');
        }
    }

}
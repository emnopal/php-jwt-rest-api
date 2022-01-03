<?php

namespace BadHabit\LoginManagement\Controller;

use BadHabit\LoginManagement\Auth\Handler;
use BadHabit\LoginManagement\Config\Database;
use BadHabit\LoginManagement\Domain\UserSession;
use BadHabit\LoginManagement\Exception\ValidationException;
use BadHabit\LoginManagement\Helper\ControllerMessage;
use BadHabit\LoginManagement\Model\UserLoginRequest;
use BadHabit\LoginManagement\Model\UserPasswordRequest;
use BadHabit\LoginManagement\Model\UserProfileUpdateIDRequest;
use BadHabit\LoginManagement\Model\UserProfileUpdateRequest;
use BadHabit\LoginManagement\Model\UserRegisterRequest;
use BadHabit\LoginManagement\Repository\LoggedInSessionData;
use BadHabit\LoginManagement\Repository\SessionRepository;
use BadHabit\LoginManagement\Repository\UserGetSession;
use BadHabit\LoginManagement\Repository\UserProfileGet;
use BadHabit\LoginManagement\Repository\UserProfileUpdateGet;
use BadHabit\LoginManagement\Repository\UserRepository;
use BadHabit\LoginManagement\Service\SessionService;
use BadHabit\LoginManagement\Service\UserService;

class UserController
{
    private UserService $userService;
    private SessionService $sessionService;
    private array $input;

    public function __construct()
    {
        $connection = Database::getConnection();
        $userRepository = new UserRepository($connection);
        $userService = new UserService($userRepository);
        $this->userService = $userService;
        $auth = new Handler();
        $sessionRepository = new SessionRepository($auth);
        $this->sessionService = new SessionService($sessionRepository, $userRepository);
        $this->input = (array)json_decode(file_get_contents('php://input'));
    }

    public function register()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $request = new UserRegisterRequest();
                $request->username = $this->input['username'];
                $request->password = $this->input['password'];
                $request->fullName = $this->input['full_name'];
                $request->email = $this->input['email'];
                $this->userService->register($request);
                $message = "User $request->username registered successfully";
                ControllerMessage::message(true, 200, $message);
            } else {
                $message = "Please log in or register to access this page";
                ControllerMessage::message(false, 405, $message);
            }
        } catch (ValidationException|\Exception $e) {
            if (!isset($request->username) || !isset($request->password) ||
                !isset($request->fullName) || !isset($request->email)) {
                ControllerMessage::message(false, 400, $e->getMessage());
            }
            ControllerMessage::message(false, 401, $e->getMessage());
        }
    }

    public function login()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {

                $request = new UserLoginRequest();
                $request->username = $this->input['username'];
                $request->password = $this->input['password'];
                $response = $this->userService->login($request);

                $session = new UserSession();
                $session->user_id = $response->user->username;
                $session->role = $response->user->role;
                $session->email = $response->user->email;
                $encodedSession = $this->sessionService->create($session); // automate create cookie

                $loggedInSessionData = new LoggedInSessionData($encodedSession->key);
                $message = "User logged in successfully";

                ControllerMessage::message(true, 200, $message, $loggedInSessionData->sessionData());

            } elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {

                if (isset($_COOKIE[SessionService::$COOKIE_NAME])){
                    $message = "User logged in successfully";
                    $loggedInSessionData = new LoggedInSessionData($_COOKIE[SessionService::$COOKIE_NAME]);
                    ControllerMessage::message(true, 200, $message, $loggedInSessionData->sessionData());
                }
                else {
                    throw new \Exception("Please log in or register to access this page");
                }

            } else {
                $message = "HTTP METHOD" . $_SERVER["REQUEST_METHOD"] . "not allowed";

                throw new \Exception($message);
            }

        } catch (ValidationException|\Exception | \TypeError $e) {

            if (!isset($request->username) || !isset($request->password)) {
                ControllerMessage::message(false, 400, $e->getMessage());
            }else if (!isset($_COOKIE[SessionService::$COOKIE_NAME])){
                ControllerMessage::message(false, 401, $e->getMessage());
            } else {
                ControllerMessage::message(false, 405, $e->getMessage());
            }


        }
    }

    public function logout()
    {
        $this->sessionService->destroy();
        $message = "User logged out successfully";

        ControllerMessage::message(true, 200, $message);
    }

    public function getProfile()
    {
        try {

            $url = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
            $url_components = parse_url($url);

            $data = new UserProfileGet($this->sessionService);
            $message = "User profile retrieved successfully";

            if (!isset($url_components["query"])) {
                ControllerMessage::message(true, 200, $message, $data->getAllUserProfile());

            } else {
                parse_str($url_components['query'], $params);

                if (property_exists($this->sessionService->current(), $params['show_only'])) {
                    ControllerMessage::message(
                        true, 200, $message, $data->getOnlyUserProfile($params['show_only'])
                    );

                } else {
                    ControllerMessage::message(false, 400, "Invalid property");
                }
            }

        } catch (ValidationException|\Exception $e) {
            ControllerMessage::message(false, 401, $e->getMessage());
        }
    }

    public function updateProfile()
    {
        try {
            $request = new UserProfileUpdateRequest();
            $request->username = $this->sessionService->current()->username;
            $request->fullName = $this->input['full_name'];
            $request->email = $this->input['email'];
            $this->userService->updateProfile($request);

            $requestID = new UserProfileUpdateIDRequest();
            $requestID->old_username = $request->username;
            $requestID->new_username = $this->input['username'];
            $response = $this->userService->updateID($requestID);

            $session = new UserSession();
            $session->user_id = $response->user->username;
            $session->role = $response->user->role;
            $session->email = $response->user->email;

            $this->sessionService->create($session); // automate create cookie

            $data = new UserProfileUpdateGet($response);
            $message = "User profile updated successfully";

            ControllerMessage::message(true, 200, $message, $data->getUpdateData());

        } catch (\Exception $e) {

            if (!isset($request->username) || !isset($request->fullName) || !isset($request->email)) {

                ControllerMessage::message(false, 400, $e->getMessage());
            }

            ControllerMessage::message(false, 401, $e->getMessage());
        }
    }

    public function getSession()
    {
        try {
            $url = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
            $url_components = parse_url($url);

            if (isset($_COOKIE[SessionService::$COOKIE_NAME])) {

                $data = new UserGetSession($_COOKIE[SessionService::$COOKIE_NAME], $this->sessionService);
                $message = "User profile retrieved successfully";

                if (!isset($url_components["query"])) {

                    ControllerMessage::message(true, 200, $message, $data->getSession());

                } else {

                    parse_str($url_components['query'], $params);

                    if ($params['decode'] == "true" || $params['decode'] == "1") {
                        ControllerMessage::message(true, 200, $message, $data->getSessionDecrypt());

                    } else if ($params['decode'] == "false" || $params['decode'] == "0") {
                        ControllerMessage::message(true, 200, $message, $data->getSession());

                    } else {
                        ControllerMessage::message(false, 400, "Invalid parameter");
                    }
                }

            } else {

                throw new \Exception();
            }

        } catch (\Exception|\TypeError) {

            $message = "No session found, please log in or register to continue";
            ControllerMessage::message(false, 401, $message);
        }
    }

    public function updatePassword()
    {
        try {
            $request = new UserPasswordRequest();
            $request->username = $this->sessionService->current()->username;
            $request->old = $this->input['old'];
            $request->new = $this->input['new'];
            $this->userService->updatePassword($request);

            $message = "User $request->username password updated successfully";

            ControllerMessage::message(true, 200, $message);

        } catch (ValidationException|\Exception $e) {

            if (!isset($request->username) || !isset($request->old) || !isset($request->new)) {

                ControllerMessage::message(false, 400, $e->getMessage());
            }

            ControllerMessage::message(false, 401, $e->getMessage());
        }
    }
}
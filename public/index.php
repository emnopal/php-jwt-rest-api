<?php

require_once __DIR__ . '/../vendor/autoload.php';

use BadHabit\LoginManagement\App\Router;
use BadHabit\LoginManagement\Config\Database;
use BadHabit\LoginManagement\Controller\AdminController;
use BadHabit\LoginManagement\Controller\HomeController;
use BadHabit\LoginManagement\Controller\UserController;
use BadHabit\LoginManagement\Middleware\AdminMiddleware;
use BadHabit\LoginManagement\Middleware\AuthMiddleware;
use BadHabit\LoginManagement\Middleware\MustLoginMiddleware;
use BadHabit\LoginManagement\Middleware\MustNotLoginMiddleware;

Database::getConnection('production');

Router::add('GET', '/', HomeController::class, 'index', [MustNotLoginMiddleware::class]);

Router::add('POST', '/users/register', UserController::class, 'register', [MustNotLoginMiddleware::class]);
Router::add('GET', '/users/login', UserController::class, 'login', [MustNotLoginMiddleware::class]);
Router::add('POST', '/users/login', UserController::class, 'login', [MustNotLoginMiddleware::class]);
Router::add('GET', '/users/logout', UserController::class, 'logout', [AuthMiddleware::class]);
Router::add('GET', '/users/profile', UserController::class, 'getProfile', [AuthMiddleware::class]);
Router::add('PUT', '/users/profile', UserController::class, 'updateProfile', [AuthMiddleware::class]);
Router::add('POST', '/users/profile', UserController::class, 'updateProfile', [AuthMiddleware::class]);
Router::add('PUT', '/users/password', UserController::class, 'updatePassword', [AuthMiddleware::class]);
Router::add('POST', '/users/password', UserController::class, 'updatePassword', [AuthMiddleware::class]);
Router::add('GET', '/users/session', UserController::class, 'getSession', [AuthMiddleware::class]);

Router::add('GET', '/admin', AdminController::class, 'index', [AdminMiddleware::class]);


Router::run();
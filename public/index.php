<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\Database;
use App\Controllers\UserController;
use App\Controllers\AuthController;
use App\Router\Router;

$db = new Database();
$userController = new UserController($db);
$authController = new AuthController($db);

$router = new Router();

// Роуты для пользователей
$router->add('POST', '/users', function() use ($userController) {
    $userController->createUser();
});
$router->add('GET', '/users/{id}', function($id) use ($userController) {
    $userController->getUser($id);
});
$router->add('PUT', '/users/{id}', function($id) use ($userController) {
    $userController->updateUser($id);
});
$router->add('DELETE', '/users/{id}', function($id) use ($userController) {
    $userController->deleteUser($id);
});

// Роут для авторизации
$router->add('POST', '/login', function() use ($authController) {
    $authController->login();
});

// Диспетчеризация запроса
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router->dispatch($method, $uri);

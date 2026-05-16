<?php
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\AuthController;

$app->group('/api/auth', function (RouteCollectorProxy $group) {
    $group->post('/login', [AuthController::class, 'login']);
    $group->post('/register', [AuthController::class, 'register']);
    $group->post('/logout', [AuthController::class, 'logout'])->add(new App\Middleware\AuthMiddleware());
    $group->get('/me', [AuthController::class, 'me'])->add(new App\Middleware\AuthMiddleware());
});
?>
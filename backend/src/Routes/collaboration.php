<?php
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\CollaborationController;

$app->group('/api/collaboration', function (RouteCollectorProxy $group) {
    $group->post('/join/{drawingId}', [CollaborationController::class, 'join'])->add(new App\Middleware\AuthMiddleware());
    $group->post('/leave/{drawingId}', [CollaborationController::class, 'leave'])->add(new App\Middleware\AuthMiddleware());
    $group->get('/{drawingId}/users', [CollaborationController::class, 'getUsers'])->add(new App\Middleware\AuthMiddleware());
    $group->post('/{drawingId}/update', [CollaborationController::class, 'update'])->add(new App\Middleware\AuthMiddleware());
});
?>
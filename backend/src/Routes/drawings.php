<?php
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\DrawingController;

$app->group('/api/drawings', function (RouteCollectorProxy $group) {
    $group->get('', [DrawingController::class, 'list'])->add(new App\Middleware\AuthMiddleware());
    $group->get('/{id}', [DrawingController::class, 'get'])->add(new App\Middleware\AuthMiddleware());
    $group->post('', [DrawingController::class, 'create'])->add(new App\Middleware\AuthMiddleware());
    $group->put('/{id}', [DrawingController::class, 'update'])->add(new App\Middleware\AuthMiddleware());
    $group->delete('/{id}', [DrawingController::class, 'delete'])->add(new App\Middleware\AuthMiddleware());
    $group->get('/{id}/versions', [DrawingController::class, 'getVersions'])->add(new App\Middleware\AuthMiddleware());
    $group->post('/{id}/versions', [DrawingController::class, 'saveVersion'])->add(new App\Middleware\AuthMiddleware());
    $group->get('/{id}/shared', [DrawingController::class, 'getSharedUsers'])->add(new App\Middleware\AuthMiddleware());
    $group->post('/{id}/share', [DrawingController::class, 'share'])->add(new App\Middleware\AuthMiddleware());
});
?>
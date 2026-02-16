<?php

use app\controllers\AdminController;
use app\controllers\CategoryController;
use app\controllers\ExchangeController;
use app\controllers\HomeController;
use app\controllers\LoginController;
use app\controllers\ObjectController;
use app\middlewares\SecurityHeadersMiddleware;
use flight\Engine;
use flight\net\Router;

/** 
 * @var Router $router 
 * @var Engine $app
 */

$ds = DIRECTORY_SEPARATOR;
$publicPath = realpath(__DIR__ . $ds . '..' . $ds . '..' . $ds . 'public');
$publicUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/');
$publicUrl = $publicUrl === '' ? '/' : $publicUrl . '/';

$app->set('app.public_path', $publicPath);
$app->set('app.public_url', $publicUrl);

if ($app->get('flight.base_url') === '/' && $publicUrl !== '/') {
    $app->set('flight.base_url', $publicUrl);
}

// This wraps all routes in the group with the SecurityHeadersMiddleware
$router->group('', function(Router $router) use ($app) {
    // User login/register/logout
    $router->get('/', [ LoginController::class, 'show' ]);
    $router->get('/login', [ LoginController::class, 'show' ]);
    $router->post('/login', [ LoginController::class, 'authenticate' ]);
    $router->post('/register', [ LoginController::class, 'register' ]);
    $router->get('/logout', [ LoginController::class, 'logout' ]);

    // Home
    $router->get('/home', [ HomeController::class, 'index' ]);

    // User objects management
    $router->get('/objects/mine', [ ObjectController::class, 'mine' ]);
    $router->get('/objects/create', [ ObjectController::class, 'createForm' ]);
    $router->post('/objects', [ ObjectController::class, 'store' ]);
    $router->get('/objects/@id/edit', [ ObjectController::class, 'editForm' ]);
    $router->post('/objects/@id', [ ObjectController::class, 'update' ]);
    $router->post('/objects/@id/delete', [ ObjectController::class, 'delete' ]);

    // Objects list & details
    $router->get('/objects', [ ObjectController::class, 'listOthers' ]);
    $router->get('/objects/search', [ ObjectController::class, 'search' ]);
    $router->get('/objects/@id', [ ObjectController::class, 'show' ]);
    $router->get('/objects/@id/history', [ ObjectController::class, 'history' ]);
    $router->post('/objects/@id/propose', [ ObjectController::class, 'propose' ]);

    // Exchanges
    $router->get('/exchanges', [ ExchangeController::class, 'index' ]);
    $router->post('/exchanges/@id/accept', [ ExchangeController::class, 'accept' ]);
    $router->post('/exchanges/@id/refuse', [ ExchangeController::class, 'refuse' ]);

    // Admin routes (login merge sur /login)
    $router->get('/admin', function() use ($app) {
        $app->redirect('/admin/stats');
    });
    $router->get('/admin/login', function() use ($app) {
        $app->redirect('/login');
    });
    $router->post('/admin/login', function() use ($app) {
        $app->redirect('/login');
    });
    $router->get('/admin/logout', function() use ($app) {
        $app->redirect('/logout');
    });

    $router->get('/admin/stats', [ AdminController::class, 'stats' ]);
    $router->get('/admin/categories', [ CategoryController::class, 'index' ]);
    $router->post('/admin/categories', [ CategoryController::class, 'store' ]);
    $router->get('/admin/categories/@id/edit', [ CategoryController::class, 'edit' ]);
    $router->post('/admin/categories/@id/update', [ CategoryController::class, 'update' ]);
    $router->post('/admin/categories/@id/delete', [ CategoryController::class, 'delete' ]);
}, [ SecurityHeadersMiddleware::class ]);

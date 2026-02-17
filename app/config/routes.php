<?php

use app\controllers\BesoinController;
use app\controllers\DonController;
use app\controllers\AuthController;
use app\controllers\VilleController;
use app\controllers\AchatController;
use app\middlewares\AuthMiddleware;
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
    $router->get('/', function() use ($app) {
        $app->redirect('/home');
    });

    // $router->get('/login', [ AuthController::class, 'showLogin' ]);
    // $router->post('/login', [ AuthController::class, 'login' ]);
    // $router->get('/register', [ AuthController::class, 'showRegister' ]);
    // $router->post('/register', [ AuthController::class, 'register' ]);
    // $router->get('/logout', [ AuthController::class, 'logout' ]);

    $router->group('', function(Router $router) {
        $router->get('/home', [ VilleController::class, 'index' ]);
        $router->get('/villes/id', [ VilleController::class, 'getById' ]);

        $router->get('/besoins', [ BesoinController::class, 'index' ]);
        $router->get('/besoins/id', [ BesoinController::class, 'getById' ]);
        $router->get('/besoins/type', [ BesoinController::class, 'getByType' ]);
        $router->get('/besoins/ville', [ BesoinController::class, 'getByVille' ]);
        $router->post('/besoins', [ BesoinController::class, 'create' ]);

        $router->get('/dons', [ DonController::class, 'index' ]);
        $router->get('/dons/id', [ DonController::class, 'getById' ]);
        $router->post('/dons', [ DonController::class, 'create' ]);

        $router->get('/achats', [ AchatController::class, 'index' ]);
        $router->post('/achats/simulate', [ AchatController::class, 'simulate' ]);
        $router->post('/achats/validate', [ AchatController::class, 'validate' ]);
        $router->get('/simulation', [ AchatController::class, 'showSimulation' ]);
        $router->post('/achats/delete-simulation', [ AchatController::class, 'deleteSimulation' ]);
        $router->post('/achats/commit-all', [ AchatController::class, 'commitAll' ]);
        $router->get('/recapitulatif', [ AchatController::class, 'showRecap' ]);
        $router->get('/api/recap', [ AchatController::class, 'recap' ]);
    }, [ AuthMiddleware::class ]);
}, [ SecurityHeadersMiddleware::class ]);

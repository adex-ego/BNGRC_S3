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


  
}, [ SecurityHeadersMiddleware::class ]);

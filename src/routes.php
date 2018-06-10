<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->group('/', function () use ($app) {

    $app->get('auth/login', \App\Controllers\Auth\LoginController::class . ':showLogin')->setName('login');
    $app->post('auth/login', \App\Controllers\Auth\LoginController::class . ':login');

})->add(new \App\Middleware\NonLoginRequiredMiddleware($container));


$app->group('/', function () use ($app) {

    $app->get('', \App\Controllers\HomeController::class . ':home')->setName('home');

    $app->get('auth/logout', \App\Controllers\Auth\LoginController::class . ':logout')->setName('logout');

})->add(new \App\Middleware\LoginRequiredMiddleware($container));
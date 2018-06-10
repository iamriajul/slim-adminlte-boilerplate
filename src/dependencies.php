<?php
use \Psr\Container\ContainerInterface as ContainerInterface;

// DIC configuration

$container = $app->getContainer();

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// ********* Your Dependency ************* //
// ----------------------------------------- //

// Twig view renderer
$container['view'] = function (ContainerInterface $c) {
    $settings = $c->get('settings')['renderer'];
    $view = new Slim\Views\Twig($settings['template_path'], [
        'cache' => false
    ]);

    $view->addExtension(new \Slim\Views\TwigExtension(
        $c->get('router'),
        $c->get('request')->getUri()
    ));

    return $view;
};

$container['db'] =  function (ContainerInterface $c) {
    $settings = $c->get('settings')['db'];
    try {
        $db = new PDO(
            "mysql:host={$settings['hostname']}; dbname={$settings['database']}",
            $settings['username'],
            $settings['password']
        );
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch(PDOException $e){
        $c['logger']->log(500, "Database Connection error: " . $e->getMessage());
        die("Database Connection error.");
    }
};
<?php
// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function ($c) {
    // set the template variables
    // Instantiate and add Slim specific extension
    $settings = $c->get('settings')['renderer'];
    $view = new \Slim\Views\Twig($settings['template_path']);
    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($c['router'], $basePath));
    return $view;
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// register csrf with container
$container['csrf'] = function ($c) {
    return new \Slim\Csrf\Guard;
};

// Service factory for the ORM
$container['db'] = function ($container) {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($container['settings']['db']);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};

// register the HomeController
$container[App\HomeController::class] = function($c) {
    $view = $c->get('renderer');
    $logger = $c->get('logger');
    $csrf = $c->get('csrf');
    // retrieve the 'view' from the container
    return new App\HomeController($view, $logger, $csrf);
};

// register the SubController
$container[App\SubController::class] = function($c) {
    $view = $c->get('renderer');
    $logger = $c->get('logger');
    $table = $c->get('db')->table('email');
    // retrieve the 'view' from the container
    return new App\SubController($view, $logger, $table);
};

// register the StatusController
$container[App\StatusController::class] = function($c) {
    $view = $c->get('renderer');
    $logger = $c->get('logger');
    // retrieve the 'view' from the container
    return new App\StatusController($view, $logger);
};

$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $c['response']
            ->withStatus(404)
            ->withHeader('Location', '/status/404');
    };
};

$container['notAllowedHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $c['response']
            ->withStatus(405)
            ->withHeader('Location', '/status/405');
    };
};

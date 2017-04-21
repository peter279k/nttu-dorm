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

// register the HomeController
$container['HomeController'] = function($c) {
    $view = $c->get('renderer');
    $logger = $c->get('logger');
    // retrieve the 'view' from the container
    return new HomeController($view, $logger);
};

// register the SubController
$container['SubController'] = function($c) {
    $view = $c->get('renderer');
    $logger = $c->get('logger');
    // retrieve the 'view' from the container
    return new SubController($view, $logger);
};

// register the StatusController
$container['StatusController'] = function($c) {
    $view = $c->get('renderer');
    $logger = $c->get('logger');
    // retrieve the 'view' from the container
    return new StatusController($view, $logger);
};

$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $c['response']
            ->withStatus(404)
            ->withHeader('Location', '/404');
    };
};

$container['notAllowedHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $c['response']
            ->withStatus(405)
            ->withHeader('Location', '/405');
    };
};

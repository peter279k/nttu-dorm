<?php
// Routes

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
$app->post('/subscribe', 'SubController:subscribe');
$app->post('/unsubscribe', 'SubController:unsubscribe');
$app->get('/{status_code}', 'StatusController:statusCode');
$app->get('/', 'HomeController:home');

<?php
// Routes

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/subscribe', App\SubController::class.':subscribe')->add($container->get('csrf'));;
$app->post('/unsubscribe', App\SubController::class.':unsubscribe');
$app->get('/send/newsletter', App\NewsletterController::class.':send');
$app->get('/confirm', App\SubController::class.':subConfirm');
$app->get('/unconfirm', App\SubController::class.':unSubConfirm');
$app->get('/status/{status_code}', App\StatusController::class.':statusCode');
$app->get('/', App\HomeController::class.':home')->add($container->get('csrf'));

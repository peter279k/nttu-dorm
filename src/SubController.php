<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class SubController {
    protected $renderer;
    protected $logger;

    public function __construct(\Slim\Views\Twig $renderer, \Monolog\Logger $logger) {
        $this->renderer = $renderer;
        $this->logger = $logger;
    }

    public function subscribe(Request $request, Response $response, array $args) {

    }

    public function unsubscribe(Request $request, Response $response, array $args) {

    }
}

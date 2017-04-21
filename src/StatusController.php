<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class StatusController {
    protected $renderer;
    protected $logger;

    public function __construct(\Slim\Views\Twig $renderer, \Monolog\Logger $logger) {
        $this->renderer = $renderer;
        $this->logger = $logger;
    }

    public function statusCode(Request $request, Response $response, array $args) {
        $resultStr = '';
        switch($args['status_code']) {
            case '404':
                $resultStr = $args['status_code'].' Not Found.';
                break;
            case '405':
                $resultStr = $args['status_code'].' Method Not Allowed.';
                break;
            default:
                $resultStr = 'Unknown Error...';
        }
        return $resultStr;
    }
}

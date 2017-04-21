<?php

namespace App;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class SubController {
    protected $renderer;
    protected $logger;

    public function __construct(\Slim\Views\Twig $renderer, \Monolog\Logger $logger, \Illuminate\Database\Query\Builder $table) {
        $this->renderer = $renderer;
        $this->logger = $logger;
        $this->table = $table;
    }

    public function subscribe(Request $request, Response $response, array $args) {
        $email = $request->getParsedBody()['email'];
        $renderStr = [];
        if(empty($request->getParsedBody()['csrf_name'])) {
            $renderStr['result_msg'] = 'missing the csrf token...';
        } else if(empty($request->getParsedBody()['csrf_value'])) {
            $renderStr['result_msg'] = 'missing the csrf token...';
        } else {
            if($email === '') {
                $renderStr['result_msg'] = '請輸入你的email！';
            } else {
                $email = filter_var($email, FILTER_VALIDATE_EMAIL);
                if(empty($email)) {
                    $renderStr['result_msg'] = '你的email不合法！';
                } else {
                    $from = 'dorm.nttu.biz admin system';
                    $sender = new EmailService('確認訂閱', $from, $email);
                    $renderStr['result_msg'] = $sender->send();
                }
            }
        }
        // Render the subscription view
        return $this->renderer->render($response, 'subscription.phtml', $renderStr);
    }

    public function subConfirm(Request $request, Response $response, array $args) {
        $email = $request->getQueryParams('email')['email'];
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        $renderStr = [];
        if(empty($email)) {
            $renderStr['result_msg'] = '信箱格式不正確';
        } else {
            $renderStr = [];
            try {
                $this->table->insert(['email' => $email]);
                $renderStr['result_msg'] = '訂閱成功！';
            } catch(\PDOException $e) {
                $msg = $e->getMessage();
                if(stristr($msg, 'Duplicate') !== false) {
                    $renderStr['result_msg'] = '此信箱已經訂閱！';
                } else {
                    $renderStr['result_msg'] = '訂閱失敗！';
                }
            }
        }
        return $this->renderer->render($response, 'subscription.phtml', $renderStr);
    }

    public function unSubConfirm(Request $request, Response $response, array $args) {
        $email = $request->getQueryParams('email')['email'];
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        $renderStr = [];
        if(empty($email)) {
            $renderStr['result_msg'] = '信箱格式不正確';
        } else {
            $renderStr = [];
            $records = $this->table->where('email', $email)->get();
            if(count($records) !== 0) {
                $this->table->where('email', $email)->delete();
                $renderStr['result_msg'] = '退訂成功！';
            } else {
                $renderStr['result_msg'] = '退訂失敗，原因：信箱不存在！';
            }
        }
        return $this->renderer->render($response, 'subscription.phtml', $renderStr);
    }
}

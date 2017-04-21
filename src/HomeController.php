<?php

namespace App;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class HomeController {
    protected $renderer;
    protected $logger;

    public function __construct(\Slim\Views\Twig $renderer, \Monolog\Logger $logger, \Slim\Csrf\Guard $csrf) {
        $this->renderer = $renderer;
        $this->logger = $logger;
        $this->csrf = $csrf;
    }
    public function home(Request $request, Response $response, array $args) {
        $this->processHome($request, $response);
    }
    private function processHome(Request $req, Response $response) {
        // CSRF token name and value
        $nameKey = $this->csrf->getTokenNameKey();
        $valueKey = $this->csrf->getTokenValueKey();
        $name = $req->getAttribute($nameKey);
        $value = $req->getAttribute($valueKey);

        // Route Root path log message
        $this->logger->info("Index-View '/' route");
        $args = $this->reqDormList();
        // Render the index view
        $args['csrf_token'] = '<input type="hidden" name="'.$nameKey.'" value="'.$name.'">'.'<input type="hidden" name="'.$valueKey.'" value="'.$value.'">';
        return $this->renderer->render($response, 'index.phtml', $args);
    }
    private function iniSpreadsheet() {
        $spreadsheet = new Spreadsheet();
        return $spreadsheet->iniSpreadsheet();
    }
    private function reqDormList() {
        $spreadsheet = $this->iniSpreadsheet();
        if(is_array($spreadsheet)) {
            return $spreadsheet;
        }
        // Get the first worksheet (tab)
        var_dump($spreadsheet);
        $worksheets = $spreadsheet->getEntries();
        $worksheet = $worksheets[0];

        $listFeed = $worksheet->getListFeed();

        /** @var ListEntry */
        $rowName = '';
        $rowValue = '';
        $count = 0;

        foreach($listFeed->getEntries() as $entry) {
            $representative = $entry->getValues();
            $rowKey = array_keys($representative);
            $theKey = array_splice($rowKey, 1);
            if($count % 2 === 1) {
                $rowValue .= '<tr class="active">';
            } else {
                $rowValue .= '<tr>';
            }
            foreach($theKey as $key) {
                if($key === '宿舍包裹編號' && $representative[$key] === '') {
                    break;
                }
                $rowValue .= '<td>'.$representative[$key].'</td>';
            }
            $rowValue .= '</tr>';
            $count += 1;
        }

        foreach($theKey as $value) {
            $rowName .= '<th>'.$value.'</th>';
        }

        $result = [
            'row_names' => $rowName,
            'row_values' => $rowValue
        ];
        file_put_contents(__DIR__.'/../templates/cache.txt', json_encode($result));

        return $result;
    }
}

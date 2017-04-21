<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Google\Spreadsheet\ServiceRequestFactory;
use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\SpreadsheetService;

class HomeController {
    protected $renderer;
    protected $logger;

    public function __construct(\Slim\Views\Twig $renderer, \Monolog\Logger $logger) {
        $this->renderer = $renderer;
        $this->logger = $logger;
    }
    public function home(Request $request, Response $response, array $args) {
        $this->processHome($response);
    }
    private function processHome(Response $response) {
        // Route Root path log message
        $this->logger->info("Index-View '/' route");
        $args = $this->reqDormList();
        // Render the index view
        return $this->renderer->render($response, 'index.phtml', $args);
    }
    private function iniSpreadsheet() {
        putenv('GOOGLE_APPLICATION_CREDENTIALS='.__DIR__.'/client_secret.json');
        $client = new Google_Client;
        $client->useApplicationDefaultCredentials();
        $client->setApplicationName('NTTU Dorm Room Mail Lists');
        $client->setScopes([
            'https://www.googleapis.com/auth/drive',
            'https://spreadsheets.google.com/feeds'
        ]);
        if($client->isAccessTokenExpired()) {
            $client->refreshTokenWithAssertion();
        }
        $accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];

        ServiceRequestFactory::setInstance(
            new DefaultServiceRequest($accessToken)
        );
        // Get our spreadsheet
        $spreadsheetService = new SpreadsheetService;
        return $spreadsheet = $spreadsheetService->getPublicSpreadsheet('1oEhwj-l7YZiCnu6CCqbY-leJ7_oSFlz3_MIWr2kZPxg');
    }
    private function reqDormList() {
        $spreadsheet = $this->iniSpreadsheet();
        // Get the first worksheet (tab)
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

        return [
            'row_names' => $rowName,
            'row_values' => $rowValue
        ];
    }
}

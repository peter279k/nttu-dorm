<?php

namespace App;

use Google\Spreadsheet\ServiceRequestFactory;
use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\SpreadsheetService;

class Spreadsheet {
    public function __construct($credential = __DIR__.'/client_secret.json', $sheetId = '1oEhwj-l7YZiCnu6CCqbY-leJ7_oSFlz3_MIWr2kZPxg') {
        $this->credential = $credential;
        $this->sheetId = $sheetId;
        putenv('GOOGLE_APPLICATION_CREDENTIALS='.$this->credential);
    }

    public function iniSpreadsheet() {
        if($this->checkCache() === false) {
            return json_decode(file_get_contents(__DIR__.'/../templates/cache.txt'), true);
        }
        $client = new \Google_Client;
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
        return $spreadsheet = $spreadsheetService->getPublicSpreadsheet($this->sheetId);
    }

    public function getSpreadsheet() {
        $client = new \Google_Client;
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
        return $spreadsheet = $spreadsheetService->getPublicSpreadsheet($this->sheetId);
    }

    private function checkCache() {
        $cachePath = __DIR__.'/../templates/cache.txt';
        $isExpire = false;
        if(file_exists($cachePath)) {
            $time = filemtime($cachePath);
            $now = time();
            if($now - $time >= 28800) {
                $isExpire = true;
            }
        } else {
            $isExpire = true;
        }
        return $isExpire;
    }
}

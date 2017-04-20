<?php

require __DIR__.'/vendor/autoload.php';

use Google\Spreadsheet\ServiceRequestFactory;
use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\SpreadsheetService;

putenv('GOOGLE_APPLICATION_CREDENTIALS='.__DIR__.'/client_secret.json');
$client = new Google_Client;
$client->useApplicationDefaultCredentials();

$client->setApplicationName('NTTU Dorm Room Mail Lists');
$client->setScopes([
    'https://www.googleapis.com/auth/drive',
    'https://spreadsheets.google.com/feeds'
]);

if ($client->isAccessTokenExpired()) {
    $client->refreshTokenWithAssertion();
}

$accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];

ServiceRequestFactory::setInstance(
    new DefaultServiceRequest($accessToken)
);

// Get our spreadsheet
$spreadsheetService = new SpreadsheetService;
$spreadsheet = $spreadsheetService->getPublicSpreadsheet('1oEhwj-l7YZiCnu6CCqbY-leJ7_oSFlz3_MIWr2kZPxg');

// Get the first worksheet (tab)
$worksheets = $spreadsheet->getEntries();
$worksheet = $worksheets[0];

$listFeed = $worksheet->getListFeed();

/** @var ListEntry */
$rowName = '';
$rowValue = '';
$count = 0;

foreach ($listFeed->getEntries() as $entry) {
    $representative = $entry->getValues();
    $rowKey = array_keys($representative);
    if($count % 2 === 1) {
       $rowValue .= '<tr class="pure-table-odd">';
    } else {
        $rowValue .= '<tr>';
    }
    foreach($representative as $key => $value) {
        $rowValue .= '<td>'.$value.'</td>';
    }
    $rowValue .= '</tr>';
    $count += 1;
}

foreach($rowKey as $value) {
    $rowName .= '<th>'.$value.'</th>';
}

/** render the result strings via Twig template engine */
$loader = new Twig_Loader_Filesystem(__DIR__.'/templates');
$twig = new Twig_Environment($loader);

echo $twig->render('index.phtml', [
    'row_names' => $rowName,
    'row_values' => $rowValue
]);

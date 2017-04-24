<?php

namespace App;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class NewsletterController {
    public function __construct(\Slim\Views\Twig $renderer, \Monolog\Logger $logger, \Illuminate\Database\Query\Builder $table) {
        $this->renderer = $renderer;
        $this->logger = $logger;
        $this->table = $table;
    }

    public function send(Request $request, Response $response) {
        $emailList = $this->table->get();
        $subject = '今日包裹通知';
        $from = 'admin nofification package';
        foreach($emailList as $assoc) {
            $index = 1;
            foreach($assoc as $key => $value) {
                $index += 1;
                if($key === 'email') {
                    $emailService = new EmailService($subject, $from, $value);
                    $mailContent = $this->genMailContent();
                    if(strlen($mailContent) !== 0) {
                        $emailService->sendNewsletter($mailContent);
                    }
                }
            }
        }
        return 'done.';
    }

    private function genMailContent() {
        $mailContent = '';
        $today = strtotime(date('Y-m-d'));
        $spreadsheet = new Spreadsheet();
        $spreadsheet = $spreadsheet->getSpreadsheet();
        $worksheets = $spreadsheet->getEntries();
        $worksheet = $worksheets[0];

        $listFeed = $worksheet->getListFeed();
        foreach($listFeed->getEntries() as $entry) {
            $representative = $entry->getValues();
            $rowKey = array_keys($representative);
            $theKey = array_splice($rowKey, 1);
            $nameString = '';
            $infoString = '';
            $liString = '';
            $templateStr = $this->templateString();
            $nameStr = $templateStr[0];
            $infoStr = $templateStr[1];

            if($representative['宿舍包裹編號'] === '') {
                break;
            }

            $receiveDate = $representative['收件日期'];
            $year = 1911 + (int)($receiveDate{0}.$receiveDate{1}.$receiveDate{2});
            $month = $receiveDate{3}.$receiveDate{4};
            $day = $receiveDate{5}.$receiveDate{6};
            $receiveDate = strtotime(date($year.'-'.$month.'-'.$day));
            if($today === $receiveDate) {
                $nameString = str_replace('[[the_name]]', $representative['收件人姓名'], $nameStr);
                $importantKey = [
                    '宿舍包裹編號', '收件日期', '送貨單位', '寢室房號', '件數', '備註'
                ];
                foreach($importantKey as $thisKey) {
                    if($representative[$thisKey] === '') {
                        $representative[$thisKey] = '無';
                    }
                    $liString .= '
                    <li>
                        <p style="margin: 10px 0;"><span style="font-size:15px">
                            <span style="color:#1c80ff">'.$thisKey.'：'.$representative[$thisKey].'</span></span>
                        </p>
                    </li>';
                }
                $infoString = str_replace('[[the_information]]', $liString, $infoStr);
                $mailContent .= $nameString.$infoString;
            }
        }
        return $mailContent;
    }

    private function templateString() {
        $nameStr = '<tr>
            <td style="word-break:break-word;font-size:0px;padding:10px 25px;padding-top:0px;padding-bottom:0px;" align="left">
               <div class="" style="cursor:auto;color:#5e6977;font-family:Arial, sans-serif;font-size:13px;line-height:22px;text-align:left;">
                  <p style="margin: 10px 0;"><span style="font-size:15px;">[[the_name]]</span></p>
               </div>
            </td>
         </tr>';
         $infoStr = ' <tr>
            <td style="word-break:break-word;font-size:0px;padding:10px 25px;padding-top:0px;padding-bottom:0px;" align="left">
               <div class="" style="cursor:auto;color:#5e6977;font-family:Arial, sans-serif;font-size:13px;line-height:18px;text-align:left;">
                  <ul>
                     [[the_information]]
                  </ul>
               </div>
            </td>
         </tr>';
        return [$nameStr, $infoStr];
    }
}

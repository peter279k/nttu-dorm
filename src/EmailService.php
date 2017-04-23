<?php

namespace App;

class EmailService {
    public function __construct($subject, $from, $to) {
        $smtpPath = __DIR__.'/smtp_secret.json';
        if(file_exists($smtpPath)) {
            $smtpArr = json_decode(file_get_contents($smtpPath), true);
            $this->smtpArr = $smtpArr;
        } else {
            $this->smtpArr = 'missing the '.$smtpPath;
        }
        $this->subject = $subject;
        $this->from = $from;
        $this->to = $to;
    }

    public function sendNewsletter($mailContent) {
        if(is_string($this->smtpArr)) {
            return 'internal error, missing the smtp_secret.json file';
        } else {
            $mailer = $this->createMailer();

            $htmlString = file_get_contents(__DIR__.'/../templates/newsletter.html');
            $htmlString = str_replace('[[email_address]]', $this->to, $htmlString);
            $htmlString = str_replace('[[newsletter_content]]', $mailContent, $htmlString);

            // Create a message
            $message = \Swift_Message::newInstance($this->subject)
                ->setFrom([$this->smtpArr['account'] => $this->from])
                ->setTo([$this->to])
                ->setContentType('text/html')
                ->setBody($htmlString)
                ->addPart('Sorry!Your email is not supported the HTML format!', 'text/plain');

            // Send the message
            $result = '';
            if($mailer->send($message) !== null) {
                $result = '我們發送了訂閱確認信件到此信箱：'.$this->to.'！';
            } else {
                $result = '寄送失敗，請稍後寄送！';
            }
            return $result;
        }
    }

    public function send() {
        if(is_string($this->smtpArr)) {
            return 'internal error, missing the smtp_secret.json file';
        } else {
            $mailer = $this->createMailer();

            $htmlString = file_get_contents(__DIR__.'/../templates/confirm.html');
            $htmlString = str_replace('[[email_address]]', $this->to, $htmlString);

            $htmlString = str_replace('[[confirm_link]]', 'http://localhost:5000/confirm?email='.$this->to, $htmlString);

            // Create a message
            $message = \Swift_Message::newInstance($this->subject)
                ->setFrom([$this->smtpArr['account'] => $this->from])
                ->setTo([$this->to])
                ->setContentType('text/html')
                ->setBody($htmlString)
                ->addPart('Sorry!Your email is not supported the HTML format!', 'text/plain');

            // Send the message
            $result = '';
            if($mailer->send($message) !== null) {
                $result = '我們發送了訂閱確認信件到此信箱：'.$this->to.'！';
            } else {
                $result = '寄送失敗，請稍後寄送！';
            }
            return $result;
        }
    }

    private function createMailer() {
         // Create the Transport
         $transport = \Swift_SmtpTransport::newInstance($this->smtpArr['host'], $this->smtpArr['port'])
            ->setUsername($this->smtpArr['account'])
            ->setPassword($this->smtpArr['password'])
            ->setEncryption('ssl');

        // Create the Mailer using your created Transport
        return $mailer = \Swift_Mailer::newInstance($transport);
    }
}

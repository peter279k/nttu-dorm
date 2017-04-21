<?php

require __DIR__.'/vendor/autoload.php';

$email = filter_input(INPUT_POST, 'email');
$studentNum = filter_input(INPUT_POST, 'student-number');

$resultString = '';

if($email === null) {
    $resultString = '請輸入 e-mail';
}

if($studentNum === null) {
    $resultString = '請輸入學號！';
}

if($email !== null && $resultString !== null) {
    /** add record to the DB */
}

/** render the result strings via Twig template engine */
$loader = new Twig_Loader_Filesystem(__DIR__.'/templates');
$twig = new Twig_Environment($loader);

echo $twig->render('subscription.phtml', [
    'result_msg' => $resultString
]);

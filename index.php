<?php
require('vendor/autoload.php');
require('WSSoapClient.php');
$configs = include('config.php');

//example soap function 1
$payload = array(
    'clientTransRef' => '000000011',
    'quote' => false
);
$funName = 'Balance';

//example soap function 2
//$payload = array(
//    'clientTransRef' => '000000010',
//    'quote' => false,
//    'recipient' => '753047831',
//    'vendorTransactionId' => '000000010',
//    'amount' => 12.00
//);
//$funName = 'Performrecharge';

//example soap function 3
//$payload = array(
//    'transId' => '000000010'
//);
//$funName = 'QueryTransaction';




$client = new \WSSoapClient($configs['host'],
    array(
        "connection_timeout" => 25,
        'location' => $configs['location']
    ));
$client->__setUsernameToken($configs['username'], $configs['password']);
$response = $client->__soapCall($funName, array($payload));

print_r($response);


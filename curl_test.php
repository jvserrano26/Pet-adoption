<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;

$handler = new CurlHandler();                      // Force raw cURL
$stack = HandlerStack::create($handler);           // Build custom stack
$client = new Client(['handler' => $stack]);       // Assign stack

$response = $client->get('https://www.googleapis.com/oauth2/v1/certs');
echo "Status: " . $response->getStatusCode();

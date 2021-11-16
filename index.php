<?php

use Reactor\HttpClient\{
    HttpClient,
    Middleware
};

require_once __DIR__ . '/vendor/autoload.php';

$client = new HttpClient(
    'http://109.68.190.183:4081'
);

$client->pushMiddleware(new Middleware\BasicAuth('test', 'crocomobi'));
$client->pushMiddleware(new Middleware\JsonBody());
$client->pushMiddleware(new Middleware\Cookies());

$response = $client->get(
    '/ajax_request/?interface=news&action=getList'
);

print_r($response['request_header']);
print_r($response['response_header']);

//preg_match_all('#Set-Cookie#i')

//$r = http_parse_headers('asd');

//$r = \http_parse_cookie('Set-Cookie: ab_test=836; expires=Thu, 16-Dec-2021 19:12:05 GMT; Max-Age=2592000');

//print_r();
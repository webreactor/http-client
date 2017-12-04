<?php

require __DIR__."/../vendor/autoload.php";

$tests_failed = 0;

//-------------------------------------------------------------------------------------------------------------------
test("Testing json endpoint GET");
$client = new \Reactor\HttpClient\HttpClient('http://localhost:9988');
$client->pushMiddleware(new \Reactor\HttpClient\Middleware\JsonBody(true));
$r = $client->get('/');
test("Responce is 200", $r['info']['http_code'], 200);
test("There is data in responce", $r['response_data']['REQUEST_SCHEME'], 'http');


//-------------------------------------------------------------------------------------------------------------------
test("Testing json endpoint GET with query");
$r = $client->get('/', array('test'=>'aaa'));
test("There is query data in responce", $r['response_data']['REQUEST_SCHEME'], 'http');
test("Generic code is 2xx", $r['info']['generic_code'], '2xx');
test("Generic code message is OK", $r['info']['generic_code_message'], "OK");

//-------------------------------------------------------------------------------------------------------------------
test("Testing json endpoint POST http query encode");
$client = new \Reactor\HttpClient\HttpClient('http://localhost:9988');
$client->pushMiddleware(new \Reactor\HttpClient\Middleware\JsonBody(false));
$r = $client->post('/', array('test'=>'aaa'), array('ttt'=>'bbb'));
test("There is query data in responce", $r['response_data']['ttt'], 'bbb');
test("Generic code is 2xx", $r['info']['generic_code'], '2xx');
test("Generic code message is OK", $r['info']['generic_code_message'], "OK");

//-------------------------------------------------------------------------------------------------------------------
test("Testing json endpoint POST json encode");
$client = new \Reactor\HttpClient\HttpClient('http://localhost:9988');
$client->pushMiddleware(new \Reactor\HttpClient\Middleware\JsonBody());
$r = $client->post('/', array('test'=>'aaa'), array('ttt'=>'bbb'));
test("There is query data in responce body", $r['response_data']['REQUEST_BODY'], '{"ttt":"bbb"}');



//-------------------------------------------------------------------------------------------------------------------
test("Testing return fields match correctly");
$client = new \Reactor\HttpClient\HttpClient('http://localhost:9988');
$r = $client->post('/', array('test'=>'aaa'), array('ttt'=>'bbb'));
test("Request header", strpos($r['request_header'], 'Host') !== false);
test("Request body", strpos($r['request_body'], 'bbb') !== false);
test("Request body is not responce", strpos($r['request_body'], 'SERVER_SOFTWARE') === false);
test("Responce header", strpos($r['response_header'], '200 OK') !== false);
test("Responce boby", strpos($r['response_body'], 'SERVER_SOFTWARE') !== false);



//-------------------------------------------------------------------------------------------------------------------
test("Testing http methods passed correct");
$client = new \Reactor\HttpClient\HttpClient('http://localhost:9988');
$client->pushMiddleware(new \Reactor\HttpClient\Middleware\JsonBody(false));

$r = $client->get('/', array('test'=>'aaa'), array('ttt'=>'bbb'));
test("Server reports method is GET", $r['response_data']['REQUEST_METHOD'], "GET");

$r = $client->post('/', array('test'=>'aaa'), array('ttt'=>'bbb'));
test("Server reports method is POST", $r['response_data']['REQUEST_METHOD'], "POST");

// $r = $client->put('/', array('test'=>'aaa'), array('ttt'=>'bbb'));
// test("Server reports method is PUT", $r['response_data']['REQUEST_METHOD'], "PUT");

// $r = $client->patch('/', array('test'=>'aaa'), array('ttt'=>'bbb'));
// test("Server reports method is PATCH", $r['response_data']['REQUEST_METHOD'], "PATCH");

// $r = $client->delete('/', array('test'=>'aaa'), array('ttt'=>'bbb'));
// test("Server reports method is DELETE", $r['response_data']['REQUEST_METHOD'], "DELETE");


//-------------------------------------------------------------------------------------------------------------------
test("Testing endpoint with 500 responce");
$client = new \Reactor\HttpClient\HttpClient('http://test.cloud.private.srvcam.com');
$r = $client->get('/');
test("Responce is 500", $r['info']['http_code'], 500);
test("Body presented", $r['response_body'], "Cloud has arrived");
test("Generic code is 5xx", $r['info']['generic_code'], '5xx');
test("Generic code message contains Server error", strpos($r['info']['generic_code_message'], 'Server') !== false);
//-------------------------------------------------------------------------------------------------------------------

test("Testing endpoint with 400 responce");
$client = new \Reactor\HttpClient\HttpClient('http://localhost:9988');
$r = $client->get('/nothing.php');
test("Responce is 404", $r['info']['http_code'], 404);
test("Generic code is 4xx", $r['info']['generic_code'], '4xx');
test("Generic code message contains Client error", strpos($r['info']['generic_code_message'], 'Client') !== false);
test("Generic code message contains method", strpos($r['info']['generic_code_message'], 'GET') !== false);
test("Generic code message contains url", strpos($r['info']['generic_code_message'], 'http://localhost:9988/nothing.php') !== false);


//-------------------------------------------------------------------------------------------------------------------
test("Testing not existing domain");
$client = new \Reactor\HttpClient\HttpClient('http://not-existing-domain');
$r = $client->get('/');
test("Responce is 0", $r['info']['http_code'], 0);
test("Body is empty", empty($r['response_body']));
test("Generic code is 0xx", $r['info']['generic_code'], '0xx');
test("Generic code message contains connection error", strpos($r['info']['generic_code_message'], 'connection') !== false);

//-------------------------------------------------------------------------------------------------------------------
test("Testing Trace Request");
$client = new \Reactor\HttpClient\HttpClient('http://localhost:9988');
$client->pushMiddleware(new \Reactor\HttpClient\Middleware\JsonBody(false));
$client->pushMiddleware(new \Reactor\HttpClient\Middleware\TraceRequest('myApp'));
$r = $client->get('/');
test("Server got proper user agent",
    preg_match('/Curl from-app:myApp cross-request-id:\w+/', $r['response_data']['HTTP_USER_AGENT']) == 1
);


//-------------------------------------------------------------------------------------------------------------------
$_SERVER['HTTP_USER_AGENT'] = "Curl from-app:XXX cross-request-id:588929ba44a848.93010539";
$client = new \Reactor\HttpClient\HttpClient('http://localhost:9988');
$client->pushMiddleware(new \Reactor\HttpClient\Middleware\JsonBody(false));
$client->pushMiddleware(new \Reactor\HttpClient\Middleware\TraceRequest('myApp'));
$r = $client->get('/');
test(
    "Trace Request is using request id",
    $r['response_data']['HTTP_USER_AGENT'],
    'Curl from-app:myApp cross-request-id:588929ba44a848.93010539'
);

//-------------------------------------------------------------------------------------------------------------------
$_SERVER['HTTP_USER_AGENT'] = "Curl from-app:XXX cross-request-id:588929ba44a848.93010539 additional:data";
$client = new \Reactor\HttpClient\HttpClient('http://localhost:9988');
$client->pushMiddleware(new \Reactor\HttpClient\Middleware\JsonBody(false));
$client->pushMiddleware(new \Reactor\HttpClient\Middleware\TraceRequest('myApp'));
$r = $client->get('/');
test(
    "Trace Request is using request id in long sting",
    $r['response_data']['HTTP_USER_AGENT'],
    'Curl from-app:myApp cross-request-id:588929ba44a848.93010539'
);


//-------------------------------------------------------------------------------------------------------------------
function test($message, $value = true, $expected = true) {
    global $tests_failed;
    if ($value !== $expected) {
        $tests_failed++;
    }
    echo "Test: {$message} - ";
    if ($value === $expected) {
        echo "\033[0;32mOK\033[0m";
    } else {
        echo "\033[0;31mFAIL\033[0m given:'{$value}' expected:'{$expected}'";
    }
    echo "\n";
}

echo "{$tests_failed} - tests failed\n";
if ($tests_failed > 0) {
    exit(1);
}

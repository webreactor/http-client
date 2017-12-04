
### HTTP Client

*Because we dont like Guzzle*

## Main features

* It is just a wrapper for CURL
* Single core class
* Supports Middleware
* Does not generate exceptions
* `print_r` friendly
* Returns a stucture containing all needed information, debug info, headers, body

## Usage
put in `composer.json` file
```json

"repositories": [
	...
	{
		"type": "vcs",
		"url": "git://github.com/webreactor/http-client.git"
	},
	...
],
"require": {
	...
	"webreactor/http-client": "^0.1.2",
	...
},

```

put in code

```php

use \Reactor\HttpClient as HttpClient;


// Created instance of the client
$client = new HttpClient\HttpClient('http://localhost:9988');

// Optionaly add some plugins
$client->pushMiddleware(new HttpClient\Middleware\JsonBody());
$client->pushMiddleware(new HttpClient\Middleware\TraceRequest('myApp'));

$response = $client->get('/');

echo $response['info']['http_code']; // will be 0 if network level error
echo $response['request_header'];
echo $response['request_body'];
echo $response['response_header'];
echo $response['response_body'];

```

or if using symfony in `services.yml`
```

utilities.http_client:
    class: Reactor\HttpClient\HttpClient
    arguments:
        - "%ADULT_CENTRO_API_TCP_ADDR%"
    calls:
        - [pushMiddleware, ['@utilities.http_client.xml_body']]

utilities.http_client.xml_body:
    class: Reactor\HttpClient\Middleware\XmlBody
    arguments:
        - false

```

## Response structure


Example:
```

Array
(
    [curl_options] => Array
        (
            [19913] => 1
            [52] => 1
            [68] => 5
            [42] => 1
            [2] => 1
            [78] => 0
            [10002] => http://localhost:9988/
            [10023] => Array
                (
                    [0] => Content-Type: application/json
                )

            [10036] => GET
        )

    [info] => Array
        (
            [url] => http://localhost:9988/
            [content_type] => text/html; charset=UTF-8
            [http_code] => 200
            [header_size] => 170
            [request_size] => 85
            [filetime] => -1
            [ssl_verify_result] => 0
            [redirect_count] => 0
            [total_time] => 0.006009
            [namelookup_time] => 0.00418
            [connect_time] => 0.004296
            [pretransfer_time] => 0.004327
            [size_upload] => 0
            [size_download] => 1121
            [speed_download] => 186553
            [speed_upload] => 0
            [download_content_length] => -1
            [upload_content_length] => 0
            [starttransfer_time] => 0.005953
            [redirect_time] => 0
            [redirect_url] => 
            [primary_ip] => 127.0.0.1
            [certinfo] => Array
                (
                )

            [primary_port] => 9988
            [local_ip] => 127.0.0.1
            [local_port] => 52548
            [request_header] => GET / HTTP/1.1
Host: localhost:9988
Accept: */*
Content-Type: application/json


            [generic_code_message] => OK
            [generic_code] => 2xx
        )

    [response_header] => HTTP/1.1 200 OK
Server: nginx/1.11.8
Date: Mon, 30 Jan 2017 19:32:55 GMT
Content-Type: text/html; charset=UTF-8
Transfer-Encoding: chunked
Connection: keep-alive


    [response_body] => // raw response body as a string
    [request_header] => GET / HTTP/1.1
Host: localhost:9988
Accept: */*
Content-Type: application/json


    [request_body] => 
    [response_data] => // optional if response body is parsed by JsonBody plugin for example

)

```

## Client methods
```
__construct($base_url = '', $curl_defaults = array())

// $curl_defaults - see curl docs http://php.net/manual/en/function.curl-setopt.php

getCurlDefaults()

pushMiddleware($middleware) // instance that extends BaseMiddleware 

get($url, $query = null)
post($url, $query = null, $body = null)
put($url, $query = null, $body = null)
patch($url, $query = null, $body = null)
delete($url, $query = null)

Result URL is `$base_url.$url`
$query - key value array (or raw string) for query data http://host/path/?query
$body - key value array (or raw string) for BODY data

```

## Architecture

The client is implemented as single class that CRUL wrapper with middleware pattern.

For lib user convinience most common HTTP methods are implemented as individual shortcut functions: `get`, `post`, `put`, `patch`, `delete`.

All shortcut functions use low level method `exec` wisch prepares curl optiona array and call curl handler through middleware wrapper.

Middleware wrapper is a method that wraps curl call where user can modify request curl options array and responce array.











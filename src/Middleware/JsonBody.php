<?php

namespace Reactor\HttpClient\Middleware;

class JsonBody extends BaseMiddleware {

    public function __construct($encode_request = true) {
        $this->encode_request = $encode_request;
    }

    public function action($request) {
        if ($this->encode_request) {
            $request[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';
            if (isset($request[CURLOPT_POSTFIELDS])) {
                $request[CURLOPT_POSTFIELDS] = json_encode($request[CURLOPT_POSTFIELDS]);
            }
        }

        $response = parent::action($request);
        if (!empty($response['response_body'])) {
            $response['response_data'] = json_decode($response['response_body'], true);
        } else {
            $response['response_data'] = null;
        }
        return $response;
    }

}

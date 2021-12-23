<?php

namespace Reactor\HttpClient;

class HttpClient {

    protected $curl_defaults = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_HEADER => true,
        CURLINFO_HEADER_OUT => true,
        CURLOPT_CONNECTTIMEOUT => 5,
    );

    protected $base_url = '';
    protected $middleware;

    public function __construct($base_url = '', $curl_defaults = array()) {
        $this->base_url = $base_url;
        $this->curl_defaults = $curl_defaults + $this->curl_defaults;
        $this->middleware = array($this, 'callCurl');
    }

    public function getCurlDefaults() {
        return $this->curl_defaults;
    }

    public function pushMiddleware($middleware) {
        $middleware->setNext($this->middleware);
        $this->middleware = array($middleware, 'action');
    }

    public function callMiddleware($request) {
        return call_user_func_array($this->middleware, array($request));
    }

    public function get($url, $query = null) {
        return $this->exec('GET', $url, $query);
    }

    public function post($url, $query = null, $body = null) {
        return $this->exec('POST', $url, $query, $body);
    }

    public function put($url, $query = null, $body = null) {
        return $this->exec('PUT', $url, $query, $body);
    }

    public function patch($url, $query = null, $body = null) {
        return $this->exec('PATCH', $url, $query, $body);
    }

    public function delete($url, $query = null) {
        return $this->exec('DELETE', $url, $query);
    }

    public function exec($method, $url, $args = array(), $body = null, $headers = array(), $curl_options = array()) {
        if (!empty($args)) {
            if (is_string($args)) {
                $url .= '?'.$args;
            } else {
                $url .= '?'.http_build_query($args);
            }
        }

        $curl_options = $curl_options + $this->curl_defaults +
            array(
                CURLOPT_URL => $this->base_url.$url,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_CUSTOMREQUEST => $method,
            );

        if (!empty($body)) {
            $curl_options[CURLOPT_POSTFIELDS] = $body;
        }
        return $this->callMiddleware($curl_options);
    }

    public function callCurl($curl_options = array()) {

        // Encode request body if it is not a string
        if (!empty($curl_options[CURLOPT_POSTFIELDS])) {
            if (!is_string($curl_options[CURLOPT_POSTFIELDS])) {
                $curl_options[CURLOPT_POSTFIELDS] = http_build_query($curl_options[CURLOPT_POSTFIELDS]);
            }
        }

        // Curl call routine
        $_curl = curl_init();
        curl_setopt_array($_curl, $curl_options);
        $raw_response = curl_exec($_curl);

        $ret = $this->prepareReturn($_curl, $curl_options, $raw_response);
        curl_close($_curl);
        return $ret;
    }

    protected function prepareReturn($_curl, $curl_options, $raw_response) {
        $info = curl_getinfo($_curl);
        $response_header = $response_body = '';
        $request_header = $request_body = '';

        if (isset($info['header_size'])) {
            $response_header = substr($raw_response, 0, $info['header_size']);
            $response_body = substr($raw_response, $info['header_size']);
        }

        if (isset($info['request_header'])) {
            $request_header = $info['request_header'];
        }

        if (isset($curl_options[CURLOPT_POSTFIELDS])) {
            $request_body = $curl_options[CURLOPT_POSTFIELDS];
        }

        return array(
            'curl_options' => $curl_options,
            'info' => $this->addSummary($_curl, $curl_options, $info),
            'response_header' => $response_header,
            'response_body' => $response_body,
            'request_header' => $request_header,
            'request_body' => $request_body,
        );
    }

    protected function addSummary($_curl, $curl_options, $info) {
        
        $http_code = $info['http_code'];
        $at = 'at '.$curl_options[CURLOPT_CUSTOMREQUEST].' '.$curl_options[CURLOPT_URL];

        if (empty($http_code)) {
            $info['generic_code_message'] = curl_error($_curl).", error $at";
            $info['generic_code'] = '0xx';
        }

        if ($http_code >= 100 && $http_code < 200) {
            $info['generic_code_message'] = "Info message ($http_code) $at";
            $info['generic_code'] = '1xx';
            return $info;
        }
        if ($http_code >= 200 && $http_code < 300) {
            $info['generic_code_message'] = "OK";
            $info['generic_code'] = '2xx';
            return $info;
        }
        if ($http_code >= 300 && $http_code < 400) {
            $info['generic_code_message'] = "Redirection or not modified ($http_code) $at";
            $info['generic_code'] = '3xx';
            return $info;
        }
        if ($http_code >= 400 && $http_code < 500) {
            $info['generic_code_message'] = "Client error ($http_code) $at";
            $info['generic_code'] = '4xx';
            return $info;
        }
        if ($http_code >= 500) {
            $info['generic_code_message'] = "Server error ($http_code) $at";
            $info['generic_code'] = '5xx';
            return $info;
        }
        return $info;
    }

}

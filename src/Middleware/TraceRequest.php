<?php

namespace Reactor\HttpClient\Middleware;

class TraceRequest extends BaseMiddleware {

    public function __construct($application = '') {
        $this->agent = $this->getAgent($application);
    }

    public function getRequestID() {
        $rid = false;
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            if (preg_match('/cross-request-id:([\w\.]+)/', $_SERVER['HTTP_USER_AGENT'], $matched)) {
                $rid = $matched[1];
            }
        }
        if ($rid === false) {
            $rid = uniqid('', true);
        }
        return $rid;
    }

    public function getAgent($application) {
        $agent = 'Curl';
        if (!empty($application)) {
            $agent .= ' from-app:'.$application;
        }
        $agent .= ' cross-request-id:'.$this->getRequestID();
        return $agent;
    }

    public function action($request) {
        $request[CURLOPT_USERAGENT] = $this->agent;
        return parent::action($request);
    }

}

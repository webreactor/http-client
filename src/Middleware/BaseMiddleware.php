<?php

namespace Reactor\HttpClient\Middleware;

class BaseMiddleware {

    public function setNext($next_callback) {
        $this->next = $next_callback;
    }

    public function action($request) {
        return call_user_func_array($this->next, array($request));
    }

}

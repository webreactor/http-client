<?php

namespace Reactor\HttpClient\Middleware;

class History extends BaseMiddleware {

    protected $data = array();

    public function get($id = null) {
        if ($id !== null) {
            return $this->data[$id];
        }
        return $this->data;
    }

    public function action($request) {
        $responce = parent::action($request);
        $this->data[] = $responce;
        return $responce;
    }

    public function reset() {
        $this->data = array();
    }

}

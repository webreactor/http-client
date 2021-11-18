<?php

namespace Reactor\HttpClient\Middleware;

class BasicAuth extends BaseMiddleware
{
    private $login;
    private $password;

    /**
     * @param  string  $login
     * @param  string  $password
     */
    public function __construct($login, $password)
    {
        $this->login = $login;
        $this->password = $password;
    }

    public function action($request)
    {
        $request[CURLOPT_USERPWD] = sprintf(
            '%s:%s',
            $this->login,
            $this->password
        );

        return parent::action($request);
    }
}

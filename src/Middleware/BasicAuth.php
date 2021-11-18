<?php

namespace Reactor\HttpClient\Middleware;

class BasicAuth extends BaseMiddleware
{
    private $login;
    private $password;

    public function __construct(string $login, string $password)
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

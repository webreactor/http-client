<?php

namespace Reactor\HttpClient\Middleware;

class Cookies extends BaseMiddleware
{
    const SESSION_KEY_FOR_COOKIES = 'reactor_http_client';

    public function __construct() {
        session_start();
    }

    public function action($request)
    {
        $sessionKey4RequestUrl = $this->getSessionKey4RequestUrl($request[CURLOPT_URL]);

        if (isset($_SESSION[self::SESSION_KEY_FOR_COOKIES][$sessionKey4RequestUrl])) {
            $request[CURLOPT_COOKIE] = $this->buildCookies4CurlOpt(
                $_SESSION[self::SESSION_KEY_FOR_COOKIES][$sessionKey4RequestUrl]
            );
        }

        $response = parent::action($request);

        $extractedCookies = $this->extractResponseHeaderCookies(
            $response['response_header']
        );

        foreach ($extractedCookies as $cookie) {
            $_SESSION[self::SESSION_KEY_FOR_COOKIES][$sessionKey4RequestUrl][$cookie['name']] = $cookie['value'];
        }

        return $response;
    }

    protected function extractResponseHeaderCookies(string $responseHeaders): array
    {
        $extractedCookies = [];
        preg_match_all('#^set-Cookie:\s*(.*)$#mi', $responseHeaders, $matchesCookieLines);

        foreach ($matchesCookieLines[1] as $matchCookieLine)
        {
            $pieces = array_filter(
                array_map(
                    'trim',
                    explode(';', $matchCookieLine)
                )
            );

            $cookieAttributesValue = $this->getDefaultCookieAttributesValue();
            foreach ($pieces as $part)
            {
                [$key, $value] = explode('=', $part, 2);

                $key = trim($key);
                $value = isset($value) ? trim($value) : true;

                if (null === $cookieAttributesValue['name']) {
                    $cookieAttributesValue['name'] = $key;
                    $cookieAttributesValue['value'] = $value;
                }
                else {
                    $key = strtolower($key);
                    if (array_key_exists($key, $cookieAttributesValue)) {
                        $cookieAttributesValue[$key] = $value;
                    }
                }

            }

            $extractedCookies[] = $cookieAttributesValue;
        }

        return $extractedCookies;
    }

    protected function getSessionKey4RequestUrl(string $requestUrl): string {
        $parsedRequestUrl = parse_url($requestUrl);
        return sprintf(
            '%s:%s',
            $parsedRequestUrl['host'],
            $parsedRequestUrl['port']
        );
    }

    protected function buildCookies4CurlOpt(array $cookies) {
        return http_build_query($cookies, '', ';');
    }

    protected function getDefaultCookieAttributesValue(): array {
        return [
            'name' => null,
            'value' => '',
            'domain' => '',
            'path' => '',
            'max-age' => null,
            'expires' => 0,
            'secure' => false,
            'httponly' => false
        ];
    }
}

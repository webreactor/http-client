<?php

namespace Reactor\HttpClient\Middleware;

class Cookies extends BaseMiddleware
{
    public function action($request)
    {
        $request[CURLOPT_COOKIE] = http_build_query($_COOKIE, '', ';');

        $response = parent::action($request);

        $extractedCookies = $this->extractResponseHeaderCookies(
            $response['response_header']
        );

        foreach ($extractedCookies as $cookie) {
            setcookie(
                $cookie['name'],
                $cookie['value'],
                strtotime($cookie['expires']),
                $cookie['path'],
                $cookie['domain'],
            );
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

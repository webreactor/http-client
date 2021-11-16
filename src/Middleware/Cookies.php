<?php

namespace Reactor\HttpClient\Middleware;

class Cookies extends BaseMiddleware
{
    public function action($request)
    {
        $response = parent::action($request);

        $extractedCookies = $this->extractResponseHeaderCookies(
            $response['response_header']
        );

        // @todo

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

            $cookieLineKeyValues = [];
            foreach ($pieces as $part) {
                [$key, $value] = explode('=', $part, 2);
                $cookieLineKeyValues[strtolower(trim($key))] = trim($value);
            }

            $extractedCookies[] = $cookieLineKeyValues;
        }

        return $extractedCookies;
    }
}

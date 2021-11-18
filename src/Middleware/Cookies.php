<?php

namespace Reactor\HttpClient\Middleware;

class Cookies extends BaseMiddleware {

    private $cookies;

    /**
     * @param  \ArrayObject  $cookies
     */
    public function __construct($cookies) {
        $this->cookies = $cookies;
    }

    public function action($request) {
        $sessionKey = $this->getSessionKey4RequestUrl($request[CURLOPT_URL]);

        if (
            isset($this->cookies[$sessionKey]) &&
            !empty($this->cookies[$sessionKey])
        ) {
            $request[CURLOPT_COOKIE] = $this->buildCookies4CurlOpt(
                $this->cookies[$sessionKey]
            );
        }

        $response = parent::action($request);

        $extractedCookies = $this->extractResponseHeaderCookies(
            $response['response_header']
        );

        foreach ($extractedCookies as $cookie) {
            $this->cookies[$sessionKey][$cookie['name']] = $cookie['value'];
        }

        return $response;
    }

    /**
     * @param  string  $responseHeaders
     * @return array
     */
    protected function extractResponseHeaderCookies($responseHeaders) {
        $extractedCookies = [];
        preg_match_all('#^Set-Cookie:\s*(.*)$#mi', $responseHeaders, $matchesCookieLines);

        foreach ($matchesCookieLines[1] as $matchCookieLine) {
            $pieces = array_filter(
                array_map(
                    'trim',
                    explode(';', $matchCookieLine)
                )
            );

            $cookieAttributesValue = $this->getDefaultCookieAttributesValue();
            foreach ($pieces as $part) {
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

    /**
     * @param  string  $requestUrl
     * @return string
     */
    protected function getSessionKey4RequestUrl($requestUrl) {
        $parsedRequestUrl = parse_url($requestUrl);
        return sprintf(
            '%s:%s',
            $parsedRequestUrl['host'],
            $parsedRequestUrl['port']
        );
    }

    /**
     * @param  array  $cookies
     * @return string
     */
    protected function buildCookies4CurlOpt($cookies) {
        return http_build_query($cookies, '', ';');
    }

    protected function getDefaultCookieAttributesValue() {
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

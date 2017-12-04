<?php

namespace Reactor\HttpClient\Middleware;

class XmlBody extends BaseMiddleware
{
    public function __construct($encode_request = true, $root_node = 'Request') {
        $this->encode_request = $encode_request;
        $this->root_node = $root_node;
    }

    public function action($request) {
        if ($this->encode_request) {
            $request[CURLOPT_HTTPHEADER][] = 'Content-Type: application/xml';
            if (isset($request[CURLOPT_POSTFIELDS])) {
                $request[CURLOPT_POSTFIELDS] = $this->buildXmlPayload($request[CURLOPT_POSTFIELDS]);
            }
        }

        $response = parent::action($request);

        if (!empty($response['response_body'])) {
            $xml = new \SimpleXMLElement($response['response_body']);
            $response['response_data'] = json_decode(json_encode($xml), true);
        } else {
            $response['response_data'] = null;
        }
        return $response;
    }   

    private function buildXmlPayload($payload) {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><' . $this->root_node . '/>');
        $this->addXmlFromArray($xml, $payload);
        return $xml->asXml();
    }   

    private function addXmlFromArray(&$xml, $payload) {
        foreach($payload as $key => $value) {
            if(is_array($value)) {
                if(!is_numeric($key)){
                    $subnode = $xml->addChild($key);
                } else {
                    $subnode = $xml->addChild('item_' . $key);
                }
                $this->addXmlFromArray($subnode, $value);
            } else {
                if(!is_numeric($key)) {
                    $xml->addChild($key, $value);
                } else {
                    $xml->addChild('item_' . $key, $value);
                }
            }
        }
    }
}

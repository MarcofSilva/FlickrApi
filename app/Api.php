<?php

namespace App;

class Api
{
    protected string $apiKey;
    public string $format;
    public string $baseUrl;

    public function __construct(
        string $apiKey,
        string $format = 'php_serial',
        string $endpoint = 'https://api.flickr.com/services/rest/'
    ) {
        $this->apiKey = $apiKey;
        $this->format = $format;
        $this->baseUrl = $endpoint;
    }

    /**
     * Send request to Api
     */
    public function request(string $method, ?array $parameters = null)
    {
        $url = $this->baseUrl . '?api_key=' . $this->apiKey . '&format=' . $this->format . '&method=' . $method;
        
        $response = file_get_contents($url . $this->parameters($parameters));

        $responseObject = unserialize($response);

        if ($responseObject['stat'] == 'ok') {
            return $responseObject;
        }

        return 'Failed request';
    }

    /**
     * Parse parameters
     */
    protected function parameters(?array $array): string
    {
        if (!is_array($array)) {
            return '';
        }

        $urlEncoded = [];

        foreach ($array as $key => $value) {
            $urlEncoded[] = urlencode($key) . '=' . urlencode($value);
        }

        return '&'.implode('&', $urlEncoded);
    }
}
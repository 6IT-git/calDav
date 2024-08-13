<?php

namespace App;

use GuzzleHttp\Client;

class HttpTools
{

    private string $url;
    private $response;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function get(string $endpoint, array $params = [], array $headers = []):self
    {
        $client = new Client([
            'base_uri' => $this->url,
            'verify' => false
        ]);
        $this->response = $client->request('GET', $endpoint, [
            'headers' => $headers
        ]);
        
        return $this;
    }

    public function post(string $endpoint, array $formData = [], array $headers = []):self
    {

        $client = new Client([
            'base_uri' => $this->url,
            'verify' => false
        ]);

        $this->response = $client->request('POST', $endpoint, [
            'headers' => $headers,
            'form_params' => $formData
        ]);

        return $this;
    }

    public function json()
    {
        // Pour traiter la réponse
        $body = $this->response->getBody();
        return json_decode($body, true);
    }
}

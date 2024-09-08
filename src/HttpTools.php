<?php

namespace App;

use GuzzleHttp\Client;

class HttpTools
{
    private $response;

    private Client $client;

    public function __construct(string $baseUrl, string $certificatePath = '')
    {
        // $this->url = $baseUrl;
        $this->client = new Client([
            'base_uri' => $baseUrl,
            'verify' => $certificatePath ? $certificatePath : false
        ]);
    }

    public function get(string $endpoint, array $params = [], array $headers = []): self
    {

        $this->response = $this->client->request('GET', $endpoint, [
            'headers' => $headers,
            'query' => $params
        ]);

        return $this;
    }

    public function post(string $endpoint, array $formData = [], array $headers = []): self
    {

        $this->response = $this->client->request('POST', $endpoint, [
            'headers' => $headers,
            'form_params' => $formData
        ]);

        return $this;
    }

    public function postJson(string $endpoint, array $payload = [], array $headers = []): self
    {

        $this->response = $this->client->request('POST', $endpoint, [
            'headers' => $headers,
            'json' => $payload
        ]);

        return $this;
    }

    public function json()
    {
        // Pour traiter la réponse
        $body = $this->response->getBody();
        return json_decode($body, true);
    }

    public function brut()
    {
        return $this->response;
    }
}

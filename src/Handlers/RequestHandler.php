<?php

namespace Mpemburn\ApiConsumer\Handlers;

use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Mpemburn\ApiConsumer\Interfaces\EndpointInterface;

class RequestHandler
{
    protected $response;

    public function send(EndpointInterface $endpoint): self
    {
        try {
            $httpClient = Http::withHeaders($endpoint->getHeaders());
            if ($endpoint->hasBasicAuth()) {
                $httpClient->withBasicAuth($endpoint->getUsername(), $endpoint->getPassword());
            }

            $this->response = $httpClient->send($endpoint->getRequestType(), $endpoint->getEndpoint());

        } catch (Exception $e) {
            $this->response = 'Error: ' . $endpoint->getRequestName() . ' responded with ' . $e->getMessage();
        }

        return $this;
    }

    public function getResponse(): string
    {
        return $this->response->body();
    }
}

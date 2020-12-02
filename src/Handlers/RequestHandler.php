<?php

namespace Mpemburn\ApiConsumer\Handlers;

use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Mpemburn\ApiConsumer\Interfaces\EndpointInterface;

class RequestHandler
{
    protected Response $response;
    protected string $errorMessage = '';

    public function send(EndpointInterface $endpoint): self
    {
        try {
            $httpClient = Http::withHeaders($endpoint->getHeaders());
            if ($endpoint->hasBasicAuth()) {
                $httpClient->withBasicAuth($endpoint->getUsername(), $endpoint->getPassword());
            }

            switch ($endpoint->getRequestType()) {
                case 'GET':
                    $this->response = $httpClient->get($endpoint->getUri(), $endpoint->getParams());
                    break;
                case 'POST':
                    $this->response = $httpClient->post($endpoint->getUri(), $endpoint->getParams());
                    break;
                case 'PUT':
                    $this->response = $httpClient->put($endpoint->getUri(), $endpoint->getParams());
                    break;
                case 'PATCH':
                    $this->response = $httpClient->patch($endpoint->getUri(), $endpoint->getParams());
                    break;
                case 'DELETE':
                    $this->response = $httpClient->delete($endpoint->getUri(), $endpoint->getParams());
                    break;
            }

        } catch (Exception $e) {
            $this->errorMessage = 'Error: ' . $endpoint->getRequestName() . ' responded with ' . $e->getMessage();
        }

        return $this;
    }

    public function getResponse(): string
    {
        return $this->response->clientError() ? $this->errorMessage :  $this->response->body();
    }
}

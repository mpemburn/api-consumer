<?php

namespace Mpemburn\ApiConsumer\Handlers;

use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Mpemburn\ApiConsumer\Interfaces\EndpointInterface;

class RequestHandler
{

    public function send(EndpointInterface $endpoint): self
    {
        try {
            $response = Http::withHeaders($endpoint->getHeaders())
                ->withBasicAuth($endpoint->getUsername(), $endpoint->getPassword())
                ->send($endpoint->getRequestType(), $endpoint->getEndpoint());
        } catch (RequestException $exception) {

        } catch (Exception $e) {
        }

        return $this;
    }
}

<?php

namespace Mpemburn\ApiConsumer\Handlers;

use Exception;
use Illuminate\Support\Facades\Http;
use Mpemburn\ApiConsumer\Interfaces\EndpointInterface;
use Mpemburn\ApiConsumer\Interfaces\ResponseHandlerInterface;
use RuntimeException;

class RequestHandler
{
    protected ResponseHandlerInterface $responseHandler;

    public function __construct(ResponseHandlerInterface $responseHandler)
    {
        $this->responseHandler = $responseHandler;
    }

    public function send(EndpointInterface $endpoint): self
    {
        $response = null;

        try {
            $httpClient = Http::withHeaders($endpoint->getHeaders());
            if ($endpoint->hasBasicAuth()) {
                $httpClient->withBasicAuth($endpoint->getUsername(), $endpoint->getPassword());
            }

            // Get the method name for the HTTP client and set it into $requestVerb
            $requestVerb = $endpoint->getRequestVerb();
            if (method_exists($httpClient, $requestVerb)) {
                $response = $httpClient->$requestVerb($endpoint->getUri(), $endpoint->getParams());
            } else {
                throw new RuntimeException('"Method ' . $requestVerb . ' does not exist in Http client."');
            }

        } catch (Exception $exception) {
            $this->responseHandler->setException($exception);
        }

        $this->responseHandler->handle($response);

        return $this;
    }

    public function getResponse(): array
    {
        return $this->responseHandler->getSuccess()
            ? $this->responseHandler->getResponseArray()
            : $this->responseHandler->getErrorMessage();
    }
}

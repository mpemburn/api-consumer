<?php

namespace Mpemburn\ApiConsumer\Handlers;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Http;
use Mpemburn\ApiConsumer\Interfaces\EndpointInterface;
use Mpemburn\ApiConsumer\Interfaces\ResponseHandlerInterface;
use RuntimeException;

class RequestManager
{
    protected ResponseHandlerInterface $responseHandler;
    protected ?string $authToken = null;
    protected bool $shouldPreseveAuthToken = false;

    public static function make(): ?self
    {
        try {
            return app()->make(__CLASS__);
        } catch (BindingResolutionException $e) {
            return null;
        }
    }

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

            if ($endpoint->usesAuthToken()) {
                $this->fetchAuthToken($endpoint);
            }

            // Get the method name for the HTTP client and set it into $requestVerb
            $requestVerb = $endpoint->getRequestVerb();
            if (method_exists($httpClient, $requestVerb)) {
                $response = $httpClient->$requestVerb($endpoint->getUri(), $endpoint->getParams());
            } else {
                // This will happen if you don't include a getRequestType method with a valid verb (i.e., GET, POST, PUT, etc.)
                throw new RuntimeException('"Method ' . $requestVerb . ' does not exist in Http client."');
            }

        } catch (Exception $exception) {
            $this->responseHandler->setException($exception);
        }

        $this->responseHandler->handle($response);

        return $this;
    }

    public function preserveAuthToken(): self
    {
        $this->shouldPreseveAuthToken = true;

        return $this;
    }

    public function getResponse(): array
    {
        return $this->responseHandler->getSuccess()
            ? $this->responseHandler->getResponseArray()
            : $this->responseHandler->getErrorMessage();
    }

    protected function fetchAuthToken(EndpointInterface $endpoint): void
    {
        /** Get an instance of the "GetAuthToken" endpoint
            It must include:
            protected ?string $authTokenFetchKeyName = [the name of the API key used to retrieve the auth token (e.g., 'key')];
            protected ?string $authTokenResponseName = [the name assigned to the auth token in the response (e.g., 'auth_token')];

            In addition, the parent class for your endpoint must include:
            protected ?string $authTokenEndpoint = GetAuthToken::class;
         */
        $authTokenEndpoint = $endpoint->getAuthTokenEndpoint();
        if (! $this->authToken && $authTokenEndpoint) {
            $response = null;

            try {
                $response = Http::get($authTokenEndpoint->getUri(), $authTokenEndpoint->getParams());
            } catch (Exception $exception) {
                $this->responseHandler->setException($exception);
            }

            $this->responseHandler->handle($response);
            $this->authToken = $this->responseHandler->getResponseKeyValue($authTokenEndpoint->getAuthTokenResponseName());

        }

        // Add the auth token to the current endpoint
        $endpoint->addAuthToken($authTokenEndpoint->getAuthTokenResponseName(), $this->authToken);

        // If we haven't told it to save the authToken, set it to null
        if (! $this->shouldPreseveAuthToken) {
            $this->authToken = null;
        }
    }
}

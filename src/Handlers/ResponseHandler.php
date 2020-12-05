<?php

namespace Mpemburn\ApiConsumer\Handlers;

use Exception;
use Illuminate\Http\Client\Response;
use Mpemburn\ApiConsumer\Interfaces\EndpointInterface;
use Mpemburn\ApiConsumer\Interfaces\ResponseHandlerInterface;

class ResponseHandler implements ResponseHandlerInterface
{
    protected ?string $responseString;
    protected array $responseArray = [];
    protected EndpointInterface $currentEndpoint;
    protected bool $success = true;
    protected Exception $exception;

    public function handle(?Response $response): void
    {
        $this->responseString = $response ? $response->body() : null;

        if ($this->responseString) {
            $this->parseJsonResponse($this->responseString);
        }
    }

    public function getSuccess(): bool
    {
        return $this->success;
    }

    public function getErrorMessage(): array
    {
        return $this->exception
            ? [
                'errorCode' => $this->exception->getCode(),
                'errorMessage' => $this->exception->getMessage()
            ]
            : [];
    }

    public function getResponseArray(): ?array
    {
        return $this->responseArray;
    }

    public function getRawResponse(): string
    {
        return $this->responseString;
    }

    public function getAccessToken(): ?string
    {
        return $this->responseArray['access_token'] ?? null;
    }

    public function getResponseCode(): ?int
    {
        return $this->responseArray['code'] ?? null;
    }

    public function getResponseMessage(): ?string
    {
        return $this->responseArray['message'] ?? null;
    }

    protected function parseJsonResponse($responseString): void
    {
        try {
            $this->responseArray = json_decode($responseString, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {
            $this->log('ResponseHandler::parseJsonResponse failed', $exception->getMessage());
        }
    }

    public function log(string $classMessage, string $errorMessage, $data = []): void
    {
    }

    public function setEndpoint(EndpointInterface $endpoint): void
    {
        $this->currentEndpoint = $endpoint;
    }

    public function setException(Exception $exception): void
    {
        $this->exception = $exception;
        $this->success = false;
    }
}

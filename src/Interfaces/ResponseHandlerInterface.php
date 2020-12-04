<?php

namespace Mpemburn\ApiConsumer\Interfaces;

use Exception;
use Illuminate\Http\Client\Response;

interface ResponseHandlerInterface
{
    public function handle(?Response $response): void;
    public function getSuccess(): bool;
    public function getErrorMessage(): array;
    public function getRawResponse(): string;
    public function getResponseCode(): ?int;
    public function getResponseMessage(): ?string;
    public function getResponseArray(): ?array;
    public function log(string $classMessage, string $errorMessage, $data = []): void;
    public function setEndpoint(EndpointInterface $endpoint): void;
    public function setException(Exception $exception): void ;

}

<?php

namespace Mpemburn\ApiConsumer\Interfaces;

interface EndpointInterface
{
    public function getUsername(): string;
    public function getPassword(): string;
    public function getRequestType(): ?string;
    public function getBaseUri(): ?string;
    public function getEndpoint(): ?string;
    public function getRequestName(): ?string;
    public function getHeaders(): array;
    public function getParams(): array;
    public function addHeader(string $headerName, string $value): EndpointInterface;
    public function addParam(string $paramName, string $value): EndpointInterface;
    public function setHeaders(array $headers): EndpointInterface;
    public function setParams(array $params): EndpointInterface;
}

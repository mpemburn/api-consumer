<?php

namespace Mpemburn\ApiConsumer\Endpoints;

use Illuminate\Support\Collection;
use Mpemburn\ApiConsumer\Interfaces\EndpointInterface;

abstract class AbstractEndpoint implements EndpointInterface
{
    protected Collection $headers;
    protected Collection $params;
    protected Collection $urlParams;
    protected string $responseParam;
    protected string $queryString;
    protected bool $concatenateParams = false;

    abstract public function getHeaders(): array;

    public function __construct()
    {
        $this->headers = collect();
        $this->params = collect();
        $this->urlParams = collect();
    }

    public function getUsername(): string
    {
        return '5ebc6a65caa3b15554c0ac3bd00f1a6a';
    }

    public function getPassword(): string
    {
        return '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';
    }

    public function getParams(): array
    {
        // Some API's (like Discourse) don't like an array of params and we need to concatenate them
        if ($this->concatenateParams) {
            $this->queryString = '?' . http_build_query($this->params->toArray());

            return [];
        }

        return $this->params->toArray();
    }

    public function getUrlParams(): array
    {
        return $this->urlParams->toArray();
    }

    public function getQueryString(): ?string
    {
        return $this->queryString;
    }

    public function addHeader(string $headerName, string $value): EndpointInterface
    {
        $this->headers->put($headerName, $value);

        return $this;
    }

    public function setHeaders(array $headers): EndpointInterface
    {
        $this->headers = collect($headers);

        return $this;
    }

    public function addParam(string $paramName, string $value): EndpointInterface
    {
        $this->params->put($paramName, $value);

        return $this;
    }

    public function setParams(array $params): EndpointInterface
    {
        $this->params = $this->params->merge(collect($params));

        return $this;
    }

    public function addUrlParam(string $paramName, string $value): EndpointInterface
    {
        $this->urlParams->put($paramName, $value);

        return $this;
    }

    public function setUrlParams(array $params): EndpointInterface
    {
        $this->urlParams = $this->urlParams->merge(collect($params));

        return $this;
    }

    protected function hydrateParams(array $params, array $argArray, array $options = []): void
    {
        // If there are options, merge them in
        $argArray = array_merge($argArray, $options);

        $params = ArrayHelper::parseParamsRecursive($params, $argArray);

        $this->setParams($params);
    }

    protected function hydrateUrlParams(string $endpoint, array $params = []): string
    {
        return ! empty($params)
            ? StringHelper::replaceVars($endpoint, $params, '{', '}')
            : $endpoint;
    }

    public function getRequestType(): ?string
    {
        return null;
    }

    public function getBaseUri(): ?string
    {
        return 'https://pemburns-explorations.myshopify.com/admin/api/2020-10';
    }

    public function getEndpoint(): ?string
    {
        // TODO: Implement getEndpoint() method.
    }

    public function getRequestName(): ?string
    {
        // TODO: Implement getRequestName() method.
    }
}

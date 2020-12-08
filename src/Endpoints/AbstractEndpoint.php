<?php

namespace Mpemburn\ApiConsumer\Endpoints;

use Illuminate\Support\Collection;
use Mpemburn\ApiConsumer\Interfaces\EndpointInterface;

abstract class AbstractEndpoint implements EndpointInterface
{
    protected Collection $headers;
    protected Collection $params;
    protected Collection $urlParams;
    protected string $queryString;
    protected bool $concatenateParams = false;
    protected ?string $authTokenEndpoint = null;
    protected ?string $authTokenFetchKeyName = null;
    protected ?string $authTokenResponseName = null;

    abstract public function getApiName(): string;

    public function __construct()
    {
        $this->headers = collect();
        $this->params = collect();
        $this->urlParams = collect();

        // If the value for the key name and API key have been specified, add to the params
        if ($this->authTokenFetchKeyName && $this->getApiKey()) {
            $this->addParam($this->authTokenFetchKeyName, $this->getApiKey());
        }
    }

    public function hasBasicAuth(): bool
    {
        return ! empty($this->getUsername()) && ! empty($this->getPassword());
    }

    public function usesAuthToken(): bool
    {
        return false;
    }

    public function getAuthTokenEndpoint(): EndpointInterface
    {
        return new $this->authTokenEndpoint();
    }

    public function getAuthTokenResponseName(): ?string
    {
        return $this->authTokenResponseName;
    }

    public function addAuthToken(string $paramName, string $authToken): void
    {
        if ($paramName && $authToken) {
            $this->addParam($paramName, $authToken);
        }
    }

    public function getBaseUri(): string
    {
        return config('api-consumer.' . $this->getApiName() . '.base_uri');
    }

    public function getApiKey(): string
    {
        return config('api-consumer.' . $this->getApiName() . '.api_key');
    }

    public function getUsername(): ?string
    {
        return config('api-consumer.' . $this->getApiName() . '.username');
    }

    public function getPassword(): ?string
    {
        return config('api-consumer.' . $this->getApiName() . '.password');
    }

    public function getHeaders(): array
    {
        return $this->headers->toArray();
    }

    public function getUri(): ?string
    {
        return $this->getBaseUri() . $this->getEndpoint();
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

    public function getRequestType(): ?string
    {
        return null;
    }

    public function getRequestVerb(): ?string
    {
        return $this->getRequestType() ? strtolower($this->getRequestType()) : null;
    }

    public function getEndpoint(): ?string
    {
        return null;
    }

    public function getRequestName(): ?string
    {
        return null;
    }

    /**
     * hydrateUrlParams
     * Used in an endpoint's getEndPoint method
     *
     * Example:
        return $this->hydrateUrlParams('/member_update/{member_id}', $this->getParams());
     ...where $params = ['member_id' => 6];
     *
     * @param string $url
     * @param array $params
     * @return string
     */
    protected function hydrateUrlParams(string $url, array $params = []): string
    {
        return preg_replace_callback('/{([\w]+)}/ix', static function ($match) use ($params) {
            return !empty($params[$match[1]]) ? $params[$match[1]] : $match[0];
        }, $url);
    }
}

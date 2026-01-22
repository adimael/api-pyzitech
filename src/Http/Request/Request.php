<?php

namespace src\Http\Request;

class Request
{
    private array $attributes = [];
    public array $params = [];

    public function __construct(
        public array $body = [],
        public array $query = [],
        public array $headers = [],
        public ?string $method = null,
        public ?string $path = null,
        public ?string $rawBody = null
    ) {}

    public function withAttribute(string $key, mixed $value): self
    {
        $clone = clone $this;
        $clone->attributes[$key] = $value;
        return $clone;
    }

    public function attribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function param(string $key): ?string
    {
        return $this->params[$key] ?? null;
    }

    public function header(string $name): ?string
    {
        foreach ($this->headers as $key => $value) {
            if (strcasecmp($key, $name) === 0) {
                return is_array($value) ? implode(',', $value) : $value;
            }
        }

        return null;
    }

    public function bearerToken(): ?string
    {
        $auth = $this->header('Authorization');
        if ($auth === null) {
            return null;
        }

        if (stripos($auth, 'Bearer ') !== 0) {
            return null;
        }

        $token = trim(substr($auth, 7));
        return $token !== '' ? $token : null;
    }

    public function toArray(): array
    {
        return [
            'body' => $this->body,
            'query' => $this->query,
            'headers' => $this->headers,
            'method' => $this->method,
            'path' => $this->path,
            'rawBody' => $this->rawBody,
        ];
    }
}

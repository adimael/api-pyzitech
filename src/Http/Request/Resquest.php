<?php

namespace src\Http\Request;

class Request
{
    public function __construct(
        public array $body = [],
        public array $query = [],
        public array $headers = [],
        public ?string $token = null,
        public ?string $method = null,
        public ?string $path = null,
        public ?string $rawBody = null
    ) {}

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
            'token' => $this->token,
            'method' => $this->method,
            'path' => $this->path,
            'rawBody' => $this->rawBody,
        ];
    }
}

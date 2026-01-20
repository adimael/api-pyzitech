<?php

namespace src\Http\Response;

class Response
{
    private int $status;
    private array $headers = [];
    private mixed $body;

    public function __construct(mixed $body = '', int $status = 200, array $headers = [])
    {
        $this->body = $body;
        $this->status = $status;
        $this->headers = $headers;
    }

    public static function json(mixed $data, int $status = 200): self
    {
        return new self(
            body: $data,
            status: $status,
            headers: [
                'Content-Type' => 'application/json; charset=utf-8'
            ]
        );
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setBody(mixed $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function Enviar(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        if (is_array($this->body) || is_object($this->body)) {
            if (!isset($this->headers['Content-Type'])) {
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode($this->body, JSON_UNESCAPED_UNICODE);
        } else {
            echo $this->body;
        }
    }
}

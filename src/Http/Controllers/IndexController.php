<?php

namespace src\Http\Controllers;
use src\Http\Response\Response;
use src\Database\PostgreSQL\Conexao;
use src\Utils\RelogioTimeZone;
use Throwable;

class IndexController
{
    public function index(): Response
    {
        $status = [
            'status' => 'ok',
            'service' => $_ENV['APP_NAME'],
            'version' => $_ENV['APP_VERSION'],
            'time' => RelogioTimeZone::agora()->format('d-m-Y H:i:s'),
            'environment' => $_ENV['APP_ENV'] ?? 'production',
            'checks' => [
                'database' => $this->checkDatabase()
            ]
        ];

        return Response::json($status, 200);
    }

    private function checkDatabase(): string
    {
        try {
            Conexao::conectar();
            return 'up';
        } catch (Throwable) {
            return 'down';
        }
    }
}
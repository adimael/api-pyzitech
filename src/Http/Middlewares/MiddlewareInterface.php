<?php

namespace src\Http\Middlewares;

use src\Http\Request\Request;
use src\Http\Response\Response;

interface MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response;
}

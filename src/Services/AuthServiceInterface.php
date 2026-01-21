<?php

namespace src\Services;

use src\Domain\Entities\Usuario;

interface AuthServiceInterface
{
    public function validate(string $token): ?Usuario;
}

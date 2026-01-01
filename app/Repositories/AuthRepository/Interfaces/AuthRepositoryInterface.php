<?php

namespace App\Repositories\AuthRepository\Interfaces;

interface AuthRepositoryInterface
{
    public function signIn(array $data);
    public function signUp(array $data);
}

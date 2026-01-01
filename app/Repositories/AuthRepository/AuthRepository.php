<?php

namespace App\Repositories\AuthRepository;

use App\Models\User;
use App\Repositories\AuthRepository\Interfaces\AuthRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthRepository implements AuthRepositoryInterface
{
    public function signIn(array $data)
    {
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The credentials are incorrect.'],
            ]);
        }
        $user ['token'] = $user->createToken('auth_token')->plainTextToken;

        return $user;
    }

    public function signUp(array $data)
    {
        $user = User::create($data);
        $user ['token'] = $user->createToken('auth_token')->plainTextToken;
        return $user;

    }
}

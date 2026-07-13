<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Регистрация нового пользователя
     *
     * @param array $data
     * @return User
     */
    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return $user;
    }

    /**
     * Аутентификация пользователя
     *
     * @param array $credentials
     * @param bool $remember
     * @return User
     * @throws ValidationException
     */
    public function login(array $credentials, bool $remember = false): User
    {
        // Извлекаем email и password, остальные поля игнорируем
        $loginCredentials = [
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ];

        // Пытаемся аутентифицировать с учётом параметра "запомнить меня"
        if (!Auth::attempt($loginCredentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => ['Неверные учетные данные.'],
            ]);
        }

        $user = Auth::user();

        // Регенерируем сессию для безопасности
        request()->session()->regenerate();

        return $user;
    }

    /**
     * Выход пользователя из системы
     *
     * @param User $user
     * @return void
     */
    public function logout(User $user): void
    {
        Auth::guard('web')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    /**
     * Получение текущего аутентифицированного пользователя
     *
     * @return User|null
     */
    public function getCurrentUser(): ?User
    {
        return Auth::user();
    }
}

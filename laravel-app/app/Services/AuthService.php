<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    /**
     * Registra novo usuário e retorna token de acesso.
     *
     * @param  array{name: string, email: string, password: string, phone?: string|null, cpf?: string|null}  $data
     * @return array{user: User, token: string}
     */
    public function register(array $data): array
    {
        $user = $this->userRepository->create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'phone'    => $data['phone'] ?? null,
            'cpf'      => $data['cpf'] ?? null,
        ]);

        event(new Registered($user));

        $token = $user->createToken('auth_token')->plainTextToken;

        return compact('user', 'token');
    }

    /**
     * Autentica usuário com e-mail e senha e retorna token de acesso.
     *
     * @param  array{email: string, password: string}  $data
     * @return array{user: User, token: string}
     *
     * @throws ValidationException
     */
    public function login(array $data): array
    {
        $user = $this->userRepository->findByEmail($data['email']);

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ])->status(401);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Sua conta está desativada. Entre em contato com o suporte.'],
            ])->status(403);
        }

        // Revogar tokens antigos (single session). Comente para multi-session.
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return compact('user', 'token');
    }

    /**
     * Revoga o token atual do usuário autenticado.
     */
    public function logout(User $user): void
    {
        $currentToken = $user->currentAccessToken();

        if ($currentToken instanceof PersonalAccessToken) {
            $currentToken->delete();
        } else {
            // Fallback: revoke all tokens (e.g. during tests with TransientToken)
            $user->tokens()->delete();
        }
    }

    /**
     * Atualiza o perfil do usuário autenticado.
     *
     * @param  array{name?: string, email?: string, phone?: string|null, current_password?: string, password?: string|null}  $data
     *
     * @throws ValidationException
     */
    public function updateProfile(User $user, array $data): User
    {
        if (isset($data['password'])) {
            if (! Hash::check($data['current_password'] ?? '', $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['A senha atual está incorreta.'],
                ]);
            }
            $data['password'] = Hash::make($data['password']);
        }

        unset($data['current_password'], $data['password_confirmation']);

        $this->userRepository->update($user, $data);

        return $user->refresh();
    }

    /**
     * Envia e-mail de recuperação de senha via broker do Laravel.
     *
     * @throws ValidationException
     */
    public function forgotPassword(string $email): void
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }
    }

    /**
     * Redefine a senha usando token enviado por e-mail.
     *
     * @param  array{token: string, email: string, password: string}  $data
     *
     * @throws ValidationException
     */
    public function resetPassword(array $data): void
    {
        $status = Password::reset(
            $data,
            static function (User $user, string $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'token' => [__($status)],
            ]);
        }
    }
}

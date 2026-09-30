<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Events\Dispatcher;

class AuthEventListener
{
    /**
     * Handle user login event.
     */
    public function handleLogin(Login $event): void
    {
        $user = $event->user;
        if ($user instanceof User) {
            ActivityLogger::logAuth(
                action: 'login',
                user: $user,
                description: "Inicio de sesión exitoso de {$user->name} ({$user->email})"
            );
        }
    }

    /**
     * Handle user logout event.
     */
    public function handleLogout(Logout $event): void
    {
        $user = $event->user;
        if ($user instanceof User) {
            ActivityLogger::logAuth(
                action: 'logout',
                user: $user,
                description: "Cierre de sesión de {$user->name} ({$user->email})"
            );
        }
    }

    /**
     * Handle failed login attempt.
     */
    public function handleFailed(Failed $event): void
    {
        $identifier = $event->credentials['email'] ?? $event->credentials['username'] ?? 'Usuario no especificado';
        $user = $event->user instanceof User ? $event->user : null;

        ActivityLogger::log(
            action: 'failed_login',
            category: 'auth',
            description: "Intento fallido de inicio de sesión ({$identifier})",
            subject: $user ?? "Acceso: {$identifier}",
            properties: [
                'identifier' => $identifier,
                'ip' => request()?->ip(),
            ],
            user: $user
        );
    }

    /**
     * Register listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'handleLogin',
            Logout::class => 'handleLogout',
            Failed::class => 'handleFailed',
        ];
    }
}

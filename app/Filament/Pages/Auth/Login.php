<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Log;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Illuminate\Validation\ValidationException;
use Filament\Facades\Filament;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Illuminate\Contracts\Auth\Authenticatable;

class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        Log::info('=== LOGIN ATTEMPT START ===');
        
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            throw ValidationException::withMessages([
                'data.email' => __('filament-panels::pages/auth/login.messages.throttled', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]),
            ]);
        }

        $data = $this->form->getState();
        $credentials = [
            'email' => $data['email'],
            'password' => $data['password'],
        ];

        Log::info('=== CREDENTIALS ===', [
            'email' => $credentials['email'],
        ]);

        // Use Laravel's Auth directly with web guard
        if (! auth()->guard('web')->attempt($credentials, $data['remember'] ?? false)) {
            Log::error('=== AUTH ATTEMPT FAILED ===');
            
            throw ValidationException::withMessages([
                'data.email' => __('filament-panels::pages/auth/login.messages.failed'),
            ]);
        }

        // Get authenticated user
        $user = auth()->guard('web')->user();
        
        Log::info('=== AUTH USER ===', [
            'user' => $user ? [
                'id' => $user->id,
                'email' => $user->email,
                'is_active' => $user->is_active,
            ] : null,
            'auth_check' => auth()->check(),
            'auth_check_web' => auth()->guard('web')->check(),
            'session_id' => session()->getId(),
            'session_keys' => array_keys(session()->all()),
        ]);

        // Check if user can access panel
        if (method_exists($user, 'canAccessPanel') && ! $user->canAccessPanel(Filament::getCurrentPanel())) {
            Log::error('=== USER CANNOT ACCESS PANEL ===', [
                'user_id' => $user->id,
                'is_active' => $user->is_active,
            ]);
            
            auth()->guard('web')->logout();

            throw ValidationException::withMessages([
                'data.email' => __('filament-panels::pages/auth/login.messages.failed'),
            ]);
        }

        Log::info('=== LOGIN SUCCESS ===', [
            'auth_check' => auth()->check(),
            'auth_check_web' => auth()->guard('web')->check(),
            'session_id' => session()->getId(),
            'session_keys' => array_keys(session()->all()),
        ]);

        // Use Filament's default authentication flow
        return app(LoginResponse::class);
    }
    
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'email' => $data['email'],
            'password' => $data['password'],
        ];
    }
}

<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        Log::info('=== LOGIN RESPONSE START ===', [
            'auth_check' => auth()->check(),
            'auth_guard_web' => auth()->guard('web')->check(),
            'user' => auth()->user() ? auth()->user()->email : null,
            'session_id' => session()->getId(),
            'session_keys' => array_keys(session()->all()),
        ]);

        // Force session save
        session()->save();
        
        Log::info('=== AFTER SESSION SAVE ===', [
            'session_keys' => array_keys(session()->all()),
            'has_login_key' => session()->has('login_web_59ba36addc2b2f9401580f014c7f58ea4e30989d'),
        ]);

        $redirectUrl = filament()->getUrl();
        
        Log::info('=== BEFORE REDIRECT ===', [
            'redirect_to' => $redirectUrl,
            'session_keys' => array_keys(session()->all()),
        ]);

        // Use response()->redirect() to ensure proper response object
        return response()->redirectTo($redirectUrl);
    }
}

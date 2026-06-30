<?php

namespace App\Http\Controllers;

use App\Mail\AccountActivationMail;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        if ($request->filled('website')) {
            return back()->withErrors(['email' => 'E-mail ou senha incorretos.'])->onlyInput('email');
        }

        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'E-mail ou senha incorretos.'])->onlyInput('email');
        }

        $user = Auth::user();

        if (! $user->isActivated()) {
            Auth::logout();
            return back()->withErrors(['email' => 'Por favor, ative sua conta pelo link enviado ao seu e-mail.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended($user->isAdmin() ? route('admin.dashboard') : route('provider.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:120',
            'email'         => 'required|email|unique:users,email',
            'business_name' => 'required|string|max:200',
            'phone'         => 'nullable|string|max:20',
        ]);

        $token = Str::random(64);

        $user = User::create([
            'name'                       => $data['name'],
            'email'                      => $data['email'],
            'password'                   => Hash::make(Str::random(32)),
            'role'                       => 'provider',
            'activation_token'           => $token,
            'activation_token_expires_at' => now()->addHours(48),
        ]);

        Provider::create([
            'user_id'       => $user->id,
            'business_name' => $data['business_name'],
            'status'        => 'pending',
        ]);

        Mail::to($user->email)->send(new AccountActivationMail($user));

        return view('auth.register-success', ['email' => $user->email]);
    }

    public function showActivate(string $token)
    {
        $user = User::where('activation_token', $token)
            ->where('activation_token_expires_at', '>', now())
            ->firstOrFail();

        return view('auth.activate', ['token' => $token, 'email' => $user->email]);
    }

    public function activate(Request $request, string $token)
    {
        $user = User::where('activation_token', $token)
            ->where('activation_token_expires_at', '>', now())
            ->firstOrFail();

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'password'                   => Hash::make($request->password),
            'email_verified_at'          => now(),
            'activation_token'           => null,
            'activation_token_expires_at' => null,
        ]);

        Auth::login($user);

        return redirect()->route('provider.dashboard');
    }
}

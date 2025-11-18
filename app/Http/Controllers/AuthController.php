<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class AuthController extends Controller
{
    // Inscription
    // Afficher le formulaire d'inscription
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    // Inscription via Blade
    public function registerWeb(Request $request)
{
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|unique:users',
        'password' => 'required|string|min:6|confirmed',
        'role' => 'required|in:mercerie,couturier',
    ]);

    // 📦 Avatar par défaut selon le rôle
    $defaultAvatar = $data['role'] === 'mercerie'
        ? 'images/avatars/mercerie.png'
        : 'images/avatars/couturier.png';

    $user = User::create([
        'name' => $data['name'],
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
        'role' => $data['role'],
        'avatar' => $defaultAvatar, // <--- ajout ici
    ]);

    // Dispatch email verification notification
    event(new Registered($user));

    return redirect()->route('login.form')->with('success', 'Inscription réussie ! Un email de confirmation vous a été envoyé.');
}

    // Show form to request password reset link
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    // Send reset link email
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
                    ? back()->with(['status' => __($status)])
                    : back()->withErrors(['email' => __($status)]);
    }

    // Show reset form
    public function showResetForm($token)
    {
        return view('auth.passwords.reset', ['token' => $token]);
    }

    // Reset password
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) use ($request) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('login.form')->with('success', __($status))
                    : back()->withErrors(['email' => [__($status)]]);
    }

    // Email verification views / actions
    public function verificationNotice()
    {
        return view('auth.verify');
    }

    public function verify(Request $request, $id, $hash)
    {
        // Allow verification via signed link even if the visitor is not authenticated.
        $user = User::find($id);
        if (! $user) {
            abort(404);
        }

        // Ensure the hash matches the user's email
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login.form');
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return redirect()->route('login.form')->with('success', 'Email vérifié avec succès. Vous pouvez maintenant vous connecter.');
    }

    public function resend(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->input('email'))->firstOrFail();
        if ($user->hasVerifiedEmail()) {
            return back()->with('info', 'Email déjà vérifié.');
        }

        $user->sendEmailVerificationNotification();
        return back()->with('success', 'Email de vérification renvoyé.');
    }


    // Afficher le formulaire de connexion
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Connexion via Blade
    public function loginWeb(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!auth()->attempt($credentials)) {
            return back()->withErrors(['email' => 'Identifiants incorrects'])->withInput();
        }

        $request->session()->regenerate();

        // If a redirect_to value was provided (from localStorage via hidden input), validate it and redirect there
        $redirectTo = $request->input('redirect_to');
        if (!empty($redirectTo)) {
            // Allow relative paths (starting with '/')
            if (strpos($redirectTo, '/') === 0) {
                return redirect($redirectTo)->with('success', 'Connexion réussie !');
            }

            // If absolute URL, allow when host matches app.url host or current request host (handles 127.0.0.1:8000, localhost variants)
            $parsed = parse_url($redirectTo);
            if ($parsed && isset($parsed['host'])) {
                $targetHost = $parsed['host'];
                $appHost = parse_url(config('app.url') ?? url('/'), PHP_URL_HOST);
                $requestHost = $request->getHost();
                if ($targetHost === $appHost || $targetHost === $requestHost) {
                    return redirect($redirectTo)->with('success', 'Connexion réussie !');
                }
            }
        }

        return redirect()->route('landing')->with('success', 'Connexion réussie !');
    }

    // Déconnexion via Blade
    public function logoutWeb(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.form')->with('success', 'Déconnexion réussie !');
    }

}

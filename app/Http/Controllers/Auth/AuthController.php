<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

/**
 * Main login ( / ) - Super Admin only.
 *   Employee      -> /employee/login
 *   any other role -> /{role-name}/login   (see RoleAuthController)
 */
class AuthController extends Controller
{
    public function index()
    {
        if (Auth::check() && Auth::user()->isSuperAdmin()) {
            return redirect()->route('home');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember_me');

        if (!Auth::attempt($credentials, $remember)) {
            return back()->withInput($request->only('email'))->with('error', 'Invalid credentials.');
        }

        $user = Auth::user();

        if ((int) $user->status !== 1) {
            Auth::logout();

            return redirect('/')->with('error', 'Your account is inactive. Please contact the administrator.');
        }

        // Only Super Admin may use this page - everyone else has a login panel of their own
        if (!$user->isSuperAdmin()) {
            $role = $user->roleModel ?? $user->roles->first();
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $panel = $role instanceof Role ? $role->loginUrl() : url('/employee/login');

            return redirect('/')->with('error', 'This login is for Super Admin only. Please sign in from your own panel: ' . $panel);
        }

        // Remember Me -> email & password cookie
        if ($remember) {
            cookie()->queue('remember_email', $request->email, 60 * 24 * 30);
            cookie()->queue('remember_password', Crypt::encryptString($request->password), 60 * 24 * 30);
        } else {
            cookie()->queue(cookie()->forget('remember_email'));
            cookie()->queue(cookie()->forget('remember_password'));
        }

        $request->session()->regenerate();
        $request->session()->forget('login_role_slug');

        return redirect()->route('home')
            ->with('success', 'Login successful. Welcome, ' . $user->name . '!');
    }

    public function logout(Request $request)
    {
        // a role-panel user goes back to *their* login page
        $slug = $request->session()->get('login_role_slug');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $to = $slug ? url('/' . $slug . '/login') : '/';

        return redirect($to)->with('success', 'Logged out successfully.');
    }
}

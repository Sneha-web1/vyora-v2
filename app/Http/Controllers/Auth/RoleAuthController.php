<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Login panel for every custom role:  /{role-name}/login
 *   e.g. role "Account Manager"  ->  /account-manager/login
 *
 * Super Admin ( / ) and Employee ( /employee/login ) keep their own pages.
 */
class RoleAuthController extends Controller
{
    protected function resolveRole(string $slug): Role
    {
        $role = Role::findByLoginSlug($slug);

        abort_if(!$role || $role->isSystem(), 404);

        return $role;
    }

    public function index(string $roleSlug)
    {
        $role = $this->resolveRole($roleSlug);

        if (Auth::check() && $this->userBelongsToRole(Auth::user(), $role)) {
            return redirect('/' . $roleSlug . '/home');
        }

        return view('role-auth.login', ['role' => $role]);
    }

    public function store(Request $request, string $roleSlug)
    {
        $role = $this->resolveRole($roleSlug);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->has('remember_me');

        if (!Auth::attempt($request->only('email', 'password'), $remember)) {
            return back()->withInput($request->only('email'))->with('error', 'Invalid credentials.');
        }

        $user = Auth::user();

        if ((int) $user->status !== 1) {
            Auth::logout();

            return back()->withInput($request->only('email'))->with('error', 'Your account is inactive. Please contact the administrator.');
        }

        // the account must really have THIS role - a user of another role can't sign in on this panel
        if (!$this->userBelongsToRole($user, $role)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/' . $roleSlug . '/login')
                ->withInput($request->only('email'))
                ->with('error', 'This account does not have access to the ' . $role->name . ' panel.');
        }

        $request->session()->regenerate();
        $request->session()->put('login_role_slug', $roleSlug);

        return redirect('/' . $roleSlug . '/home')->with('success', 'Welcome, ' . $user->name . '!');
    }

    protected function userBelongsToRole($user, Role $role): bool
    {
        return (int) $user->role_id === (int) $role->id || $user->hasRole($role->name);
    }
}

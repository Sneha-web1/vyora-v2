<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Role;
use App\Models\User;
use App\Support\MenuRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Roles & Permissions - ONE page, three steps:
 *   1. Role   2. User   3. Menu access (View / Add / Edit / Delete per menu)
 */
class AccessController extends Controller
{
    public function index(Request $request, MenuRegistry $registry)
    {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('role_menu', 'can_add')
            || !\Illuminate\Support\Facades\Schema::hasColumn('role_menu', 'can_delete')
            || !\Illuminate\Support\Facades\Schema::hasColumn('roles', 'description')) {
            abort(500, 'Run "php artisan migrate" first (adds Add/Delete + description columns), then reload this page.');
        }

        // auto-scan: menus added to config/menus.php appear here by themselves
        $newMenus = $registry->scanIfChanged();
        if ($newMenus > 0) {
            session()->now('success', "{$newMenus} new menu(s) found and added. Tick them for the roles that need them.");
        }

        $roles = Role::orderBy('name')->get()
            ->sortBy(fn ($r) => $r->isSuperAdminRole() ? 0 : ($r->isEmployeeRole() ? 1 : 2))
            ->values();

        $userCounts = User::selectRaw('role_id, count(*) as total')->groupBy('role_id')->pluck('total', 'role_id');
        $menuCounts = DB::table('role_menu')->where('can_view', 1)->selectRaw('role_id, count(*) as total')
            ->groupBy('role_id')->pluck('total', 'role_id');

        $selected = $roles->firstWhere('id', (int) $request->query('role'))
            ?? $roles->first(fn ($r) => !$r->isSystem())
            ?? $roles->first();
        $roleMenuCounts = $menuCounts;

        $users = $selected
            ? User::where('role_id', $selected->id)->orderBy('name')->get(['id', 'name', 'email', 'phone', 'status'])
            : collect();

        $menus = Menu::where('status', 1)->orderBy('order')->get();
        $given = $selected
            ? DB::table('role_menu')->where('role_id', $selected->id)->get()->keyBy('menu_id')
            : collect();

        // flat, ordered rows (parent first, then its children) with depth
        $byParent = $menus->groupBy(fn ($m) => $m->parent_id ?? 0);
        $rows = [];
        $walk = function ($parentId, $depth) use (&$walk, &$rows, $byParent, $given) {
            foreach ($byParent->get($parentId, collect()) as $m) {
                $g = $given->get($m->id);
                $kids = $byParent->get($m->id, collect());
                $rows[] = [
                    'id'       => $m->id,
                    'parent'   => $m->parent_id,
                    'title'    => $m->title,
                    'depth'    => $depth,
                    'group'    => $kids->isNotEmpty(),
                    'path'     => ($m->route && $m->route !== '#') ? (Menu::pathOf($m->route)) : null,
                    'view'     => (bool) ($g->can_view ?? 0),
                    'add'      => (bool) ($g->can_add ?? 0),
                    'edit'     => (bool) ($g->can_edit ?? 0),
                    'delete'   => (bool) ($g->can_delete ?? 0),
                    'section'  => $m->section,
                ];
                $walk($m->id, $depth + 1);
            }
        };
        $walk(0, 0);

        $allUsers = User::with('roleModel:id,name')->whereNotNull('role_id')
            ->whereNotIn('role_id', $roles->filter->isEmployeeRole()->pluck('id'))
            ->orderBy('name')->limit(500)->get(['id', 'name', 'email', 'phone', 'status', 'role_id']);

        $stats = [
            'totalRoles'  => $roles->count(),
            'activeRoles' => $roles->count(),
            'usersWithRole' => User::whereNotNull('role_id')->count(),
            'activeMenus' => $menus->count(),
        ];
        $tab = in_array($request->query('tab'), ['role', 'user', 'perm'], true) ? $request->query('tab') : 'role';

        return view('access.index', [
            'title'      => 'Roles & Permissions',
            'stats'      => $stats,
            'allUsers'   => $allUsers,
            'tab'        => $tab,
            'roles'      => $roles,
            'selected'   => $selected,
            'userCounts' => $userCounts,
            'menuCounts' => $menuCounts,
            'users'      => $users,
            'rows'       => $rows,
            'totalMenus' => $menus->count(),
        ]);
    }

    /* ---------------- Step 1 : role ---------------- */

    public function storeRole(Request $request)
    {
        $data = $request->validate([
            'role_name' => ['required', 'string', 'max:60', Rule::unique('roles', 'name')],
            'description' => 'nullable|string|max:500',
        ], [
            'role_name.unique' => 'A role with this name already exists.',
        ]);

        $name = trim($data['role_name']);
        $slug = Role::slugFor($name);
        if ($slug === '' || in_array($slug, Role::RESERVED_SLUGS, true)) {
            return back()->withInput()->with('error', "'{$name}' cannot be used as a role name. Please choose another one.");
        }
        foreach (\Illuminate\Support\Facades\Route::getRoutes() as $rt) {
            if ((explode('/', trim($rt->uri(), '/'))[0] ?? '') === $slug) {
                return back()->withInput()->with('error', "'{$name}' clashes with an existing page address (/{$slug}). Please choose another name.");
            }
        }
        if (Role::findByLoginSlug($slug)) {
            return back()->withInput()->with('error', 'A role with a similar name already exists.');
        }

        $role = Role::create(['name' => $name, 'guard_name' => 'web', 'description' => $data['description'] ?? null]);

        return redirect()->route('access.index', ['role' => $role->id, 'tab' => 'user'])
            ->with('success', "Role '{$role->name}' created. Now create a user for it.");
    }

    public function updateRole(Request $request, Role $role)
    {
        $data = $request->validate([
            'role_name'   => ['required', 'string', 'max:60', Rule::unique('roles', 'name')->ignore($role->id)],
            'description' => 'nullable|string|max:500',
        ]);

        $role->description = $data['description'] ?? null;
        if (!$role->isSystem()) {
            $slug = Role::slugFor($data['role_name']);
            if ($slug === '' || in_array($slug, Role::RESERVED_SLUGS, true)) {
                return back()->with('error', 'This role name cannot be used.');
            }
            $role->name = trim($data['role_name']);
        }
        $role->save();

        return redirect()->route('access.index', ['role' => $role->id])->with('success', "Role '{$role->name}' updated.");
    }

    public function destroyRole(Role $role)
    {
        if ($role->isSystem()) {
            return back()->with('error', "'{$role->name}' is a system role and cannot be deleted.");
        }
        if (User::where('role_id', $role->id)->exists()) {
            return back()->with('error', 'This role still has users. Remove or move them first.');
        }

        DB::table('role_menu')->where('role_id', $role->id)->delete();
        $name = $role->name;
        $role->delete();

        return redirect()->route('access.index')->with('success', "Role '{$name}' deleted.");
    }

    /* ---------------- Step 2 : user ---------------- */

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'phone'    => 'nullable|string|max:30',
            'password' => 'required|string|min:8',
            'role_id'  => 'required|exists:roles,id',
        ], [
            'email.unique'    => 'This email address is already registered.',
            'password.min'    => 'Password must be at least 8 characters.',
            'role_id.required' => 'Please pick a role first.',
        ]);

        $role = Role::findOrFail($data['role_id']);
        if ($role->isEmployeeRole()) {
            return back()->withInput()->with('error', 'Employees are created from Employees > New Employee.');
        }

        $user = User::create([
            'name'     => trim($data['name']),
            'email'    => strtolower(trim($data['email'])),
            'phone'    => !empty($data['phone']) ? trim($data['phone']) : null,
            'password' => Hash::make($data['password']),
            'role_id'  => $role->id,
            'status'   => 1,
        ]);
        $user->assignRole($role->name);

        return redirect()->route('access.index', ['role' => $role->id, 'tab' => 'user'])
            ->with('success', "User '{$user->name}' created with role '{$role->name}'.");
    }

    public function destroyUser(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $roleId = $user->role_id;
        $name = $user->name;
        $user->delete();

        return redirect()->route('access.index', ['role' => $roleId])->with('success', "User '{$name}' removed.");
    }

    /* ---------------- Step 3 : menu access ---------------- */

    public function saveAccess(Request $request)
    {
        $request->validate([
            'role_id'       => 'required|exists:roles,id',
            'perms'         => 'nullable|array',
            'perms.*'       => 'array',
        ]);

        $role = Role::findOrFail($request->role_id);
        if ($role->isSuperAdminRole()) {
            return response()->json(['status' => false, 'message' => 'Super Admin always has full access and cannot be changed.'], 422);
        }

        $menus = Menu::get(['id', 'parent_id'])->keyBy('id');
        $payload = [];

        foreach ((array) $request->input('perms', []) as $menuId => $p) {
            $menuId = (int) $menuId;
            if (!$menus->has($menuId)) {
                continue;
            }
            $add = !empty($p['add']);
            $edit = !empty($p['edit']);
            $delete = !empty($p['delete']);
            $view = !empty($p['view']) || $add || $edit || $delete; // any action needs view
            if (!$view) {
                continue;
            }
            $payload[$menuId] = [
                'can_view' => 1, 'can_add' => (int) $add, 'can_edit' => (int) $edit, 'can_delete' => (int) $delete,
            ];
        }

        // parents of every ticked menu must be present, otherwise the sidebar can't show the child
        foreach (array_keys($payload) as $menuId) {
            $parentId = $menus[$menuId]->parent_id;
            while ($parentId && !isset($payload[$parentId])) {
                $payload[$parentId] = ['can_view' => 1, 'can_add' => 0, 'can_edit' => 0, 'can_delete' => 0];
                $parentId = $menus[$parentId]->parent_id ?? null;
            }
        }

        $role->menus()->sync($payload);

        return response()->json([
            'status'  => true,
            'message' => "Access for '{$role->name}' saved (" . count($payload) . ' menus).',
        ]);
    }

    /* ---------------- Scan ---------------- */

    public function scan(MenuRegistry $registry)
    {
        $new = $registry->scanIfChanged(true);

        return back()->with('success', $new > 0
            ? "{$new} new menu(s) found and added."
            : 'Scan finished - no new menus found.');
    }
}

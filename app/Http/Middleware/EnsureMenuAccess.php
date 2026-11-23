<?php

namespace App\Http\Middleware;

use App\Models\Menu;
use App\Support\MenuRegistry;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Server-side check behind the sidebar: a role can only open the pages of the menus
 * that were given to it in User Management > Menu Access.
 *
 * - Super Admin: always allowed.
 * - A route is "protected" when its name equals / starts with the route of any menu in the DB.
 *   Routes that belong to no menu (ajax helpers, dropdown loaders, dashboard ...) stay open.
 * - A protected route is allowed only if one of the role's own menus covers it.
 */
class EnsureMenuAccess
{
    /** always reachable for every logged-in user */
    protected array $open = ['home', 'dashboard', 'logout'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $name = $request->route()?->getName();

        // custom-role users: every generated link / redirect gets their panel prefix  (/test/master/company)
        if ($user && !$user->isSuperAdmin()) {
            $r = $user->roleModel ?: $user->roles->first();
            if ($r && !$r->isSystem()) {
                $slug = $r->loginSlug();
                \Illuminate\Support\Facades\URL::formatPathUsing(
                    fn ($path, $route = null) => $route ? '/' . $slug . $path : $path
                );
            }
        }

        if ($user && $user->isSuperAdmin()) {
            view()->share('menuPerms', ['view' => true, 'add' => true, 'edit' => true, 'delete' => true]);

            return $next($request);
        }

        if (!$user || !$name || in_array($name, $this->open, true)) {
            return $next($request);
        }

        $allMenuRoutes = $this->routesOf(Menu::query());

        // not part of any menu -> not controlled by menu access
        if (!$this->matches($name, $allMenuRoutes)) {
            return $next($request);
        }

        $roleId = $user->role_id ?: $user->roles->first()?->id;
        $ownedMenuIds = DB::table('role_menu')->where('role_id', $roleId)->where('can_view', 1)->pluck('menu_id');
        $ownRoutes = $this->routesOf(Menu::query()->whereIn('id', $ownedMenuIds)->where('status', 1));

        if ($this->matches($name, $ownRoutes)) {
            // View is allowed -> now check Add / Edit / Delete of that menu
            $perms = app(MenuRegistry::class)->permissionsFor($user, $name);
            if ($perms) {
                view()->share('menuPerms', $perms);
                $need = $this->actionOf($request, $name);
                if ($need !== 'view' && empty($perms[$need])) {
                    $label = ['add' => 'add', 'edit' => 'edit', 'delete' => 'delete'][$need];
                    $msg = "You do not have permission to {$label} here.";
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json(['success' => false, 'status' => false, 'message' => $msg], 403);
                    }

                    return back()->with('error', $msg);
                }
            }

            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'You do not have access to this page.'], 403);
        }

        return redirect()->route('home')->with('error', 'You do not have access to that page.');
    }

    /** view | add | edit | delete  - guessed from the HTTP verb and the route name */
    protected function actionOf(Request $request, string $name): string
    {
        $parts = explode('.', strtolower($name));
        $last = end($parts);
        $method = $request->method();

        if ($method === 'DELETE' || preg_match('/(destroy|delete|remove)/', $last)) {
            return 'delete';
        }
        if (in_array($method, ['PUT', 'PATCH'], true) || preg_match('/(^|[-_])(edit|update|toggle|status|respond|complete|verify)|^(map|unmap)subordinate/', $last)) {
            return 'edit';
        }
        if (preg_match('/^(create|store|add|import|upload)/', $last)) {
            return 'add';
        }

        return 'view';
    }

    protected function routesOf($query): array
    {
        return $query->whereNotNull('route')
            ->where('route', '!=', '#')
            ->pluck('route')
            ->unique()
            ->values()
            ->all();
    }

    protected function matches(string $routeName, array $menuRoutes): bool
    {
        foreach ($menuRoutes as $menuRoute) {
            if (MenuRegistry::covers($menuRoute, $routeName)) {
                return true;
            }
        }

        return false;
    }
}

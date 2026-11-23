<?php

namespace App\Support;

use App\Models\Menu;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

/**
 * Keeps the `menus` table in sync with config/menus.php and builds the
 * sidebar tree for a given user.
 */
class MenuRegistry
{
    /**
     * Pages that belong to a menu but whose route names do not start with the menu's own route
     * (e.g. menu "Employee Directory" = employees.index, but edit/update/delete are employees.edit ...).
     * Entry ending with "." = prefix, otherwise exact route name.
     */
    public const ROUTE_FAMILIES = [
        'employees.index' => [
            'employees.show', 'employees.edit', 'employees.update', 'employees.delete', 'employees.status',
            'employees.mapSubordinates', 'employees.unmapSubordinate',
        ],
        'employees.create' => ['employees.store', 'employees.import'],
        'employees.documents.index' => ['employees.documents.'],
        'admin.resignation.index' => ['admin.resignation.'],
        'employee-report.index' => ['employee-report.'],
    ];

    /** Does the menu that points to $menuRoute control the page named $routeName ? */
    public static function covers(string $menuRoute, string $routeName): bool
    {
        if ($routeName === $menuRoute || str_starts_with($routeName, $menuRoute . '.')) {
            return true;
        }

        foreach (self::ROUTE_FAMILIES[$menuRoute] ?? [] as $entry) {
            if (str_ends_with($entry, '.') ? str_starts_with($routeName, $entry) : ($routeName === $entry || str_starts_with($routeName, $entry . '.'))) {
                return true;
            }
        }

        return false;
    }

    /** @var array<string,int> */
    protected array $seen = [];

    protected array $stats = ['created' => 0, 'updated' => 0, 'removed' => 0];

    /**
     * Push config/menus.php into the database (idempotent - safe to run any number of times).
     *
     * @return array{created:int,updated:int,removed:int,total:int}
     */
    public function sync(bool $prune = false, bool $fresh = false): array
    {
        $this->seen = [];
        $this->stats = ['created' => 0, 'updated' => 0, 'removed' => 0];

        DB::transaction(function () use ($prune, $fresh) {
            if ($fresh) {
                DB::table('role_menu')->delete();
                // children first so the self-referencing FK never blocks the delete
                Menu::whereNotNull('parent_id')->delete();
                Menu::query()->delete();
            }

            $this->walk(config('menus', []), null);

            if ($prune) {
                $stale = Menu::whereNotNull('menu_key')->whereNotIn('id', array_values($this->seen))->get();
                foreach ($stale as $menu) {
                    $menu->delete(); // cascades to children and role_menu
                    $this->stats['removed']++;
                }
            }

            $this->grantSuperAdmin();
            $this->ensureParentsMapped();
        });

        return $this->stats + ['total' => count($this->seen)];
    }

    /**
     * "Scan" for new menus: pushes config/menus.php into the DB only when the file changed
     * since the last scan (cheap md5 check, safe to call on every Roles & Permissions visit).
     * New menus are never ticked for any role automatically - only Super Admin gets them.
     *
     * @return int number of newly created menus
     */
    public function scanIfChanged(bool $force = false): int
    {
        $hash = md5(json_encode(config('menus', [])));
        $cacheKey = 'menus.config.hash';

        if (!$force && \Illuminate\Support\Facades\Cache::get($cacheKey) === $hash && Menu::exists()) {
            return 0;
        }

        $result = $this->sync(false, false);
        \Illuminate\Support\Facades\Cache::forever($cacheKey, $hash);

        return (int) $result['created'];
    }

    /**
     * What may this user do on the page behind $routeName ?
     * Returns null when the route is not part of any menu (=> nothing to restrict).
     *
     * @return array{view:bool,add:bool,edit:bool,delete:bool}|null
     */
    public function permissionsFor(?User $user, ?string $routeName): ?array
    {
        if (!$user || !$routeName) {
            return null;
        }
        $all = ['view' => true, 'add' => true, 'edit' => true, 'delete' => true];
        if ($user->isSuperAdmin()) {
            return $all;
        }

        $roleId = $user->role_id ?: $user->roles->first()?->id;

        // only menus that were given to this role; the most specific route wins
        $rows = DB::table('menus')
            ->join('role_menu', 'role_menu.menu_id', '=', 'menus.id')
            ->where('role_menu.role_id', $roleId)
            ->where('role_menu.can_view', 1)
            ->where('menus.status', 1)
            ->whereNotNull('menus.route')->where('menus.route', '!=', '#')
            ->get(['menus.route', 'role_menu.can_add', 'role_menu.can_edit', 'role_menu.can_delete']);

        $best = null;
        foreach ($rows as $row) {
            if (self::covers($row->route, $routeName)) {
                if (!$best || strlen($row->route) > strlen($best->route)) {
                    $best = $row;
                }
            }
        }
        if (!$best) {
            return null;
        }

        return [
            'view'   => true,
            'add'    => (bool) $best->can_add,
            'edit'   => (bool) $best->can_edit,
            'delete' => (bool) $best->can_delete,
        ];
    }

    protected function walk(array $nodes, ?int $parentId): void
    {
        foreach (array_values($nodes) as $index => $node) {
            if (empty($node['key']) || empty($node['title'])) {
                throw new \InvalidArgumentException('Every menu in config/menus.php needs a "key" and a "title".');
            }
            if (isset($this->seen[$node['key']])) {
                throw new \InvalidArgumentException("Duplicate menu key in config/menus.php: {$node['key']}");
            }

            $permissionId = null;
            if (!empty($node['permission'])) {
                $permissionId = Permission::firstOrCreate(['name' => $node['permission'], 'guard_name' => 'web'])->id;
            }

            $attributes = [
                'section'       => $parentId ? null : ($node['section'] ?? null),
                'title'         => $node['title'],
                'icon'          => $node['icon'] ?? null,
                'route'         => $node['route'] ?? '#',
                'parent_id'     => $parentId,
                'permission_id' => $permissionId,
                'order'         => ($index + 1) * 10,
            ];

            $menu = Menu::where('menu_key', $node['key'])->first();

            if ($menu) {
                $menu->fill($attributes);
                if ($menu->isDirty()) {
                    $menu->save();
                    $this->stats['updated']++;
                }
            } else {
                // `status` is only set on create, so a menu switched off in the DB stays off
                $menu = Menu::create($attributes + ['menu_key' => $node['key'], 'status' => 1]);
                $this->stats['created']++;
            }

            $this->seen[$node['key']] = $menu->id;

            if (!empty($node['children'])) {
                $this->walk($node['children'], $menu->id);
            }
        }
    }

    /**
     * When a menu is moved under another parent, roles that already had it must also get the new
     * parent (view only), otherwise the sidebar would hide the child.
     */
    protected function ensureParentsMapped(): void
    {
        $parents = Menu::whereNotNull('parent_id')->pluck('parent_id', 'id');
        $rows = DB::table('role_menu')->where('can_view', 1)->get(['role_id', 'menu_id']);
        $have = $rows->map(fn ($r) => $r->role_id . ':' . $r->menu_id)->flip();
        $now = now();
        $insert = [];

        foreach ($rows as $r) {
            $pid = $parents[$r->menu_id] ?? null;
            while ($pid) {
                $k = $r->role_id . ':' . $pid;
                if (!isset($have[$k])) {
                    $insert[$k] = ['role_id' => $r->role_id, 'menu_id' => $pid, 'can_view' => 1, 'created_at' => $now, 'updated_at' => $now];
                    $have[$k] = true;
                }
                $pid = $parents[$pid] ?? null;
            }
        }
        if ($insert) {
            DB::table('role_menu')->insert(array_values($insert));
        }
    }

    /** Super Admin always owns every menu (also keeps the Menu Access screen consistent). */
    protected function grantSuperAdmin(): void
    {
        $role = Role::where('name', Role::SUPER_ADMIN)->first();
        if (!$role) {
            return;
        }

        // works even before `php artisan migrate` adds can_add / can_delete
        $full = ['can_view' => 1, 'can_edit' => 1];
        if (\Illuminate\Support\Facades\Schema::hasColumn('role_menu', 'can_add')) {
            $full['can_add'] = 1;
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('role_menu', 'can_delete')) {
            $full['can_delete'] = 1;
        }
        $payload = Menu::pluck('id')->mapWithKeys(fn ($id) => [$id => $full])->all();
        $role->menus()->sync($payload);
    }

    /* --------------------------------------------------------------
     |  Sidebar tree
     * ------------------------------------------------------------ */

    /**
     * Nested array of menus the user may see:
     *   [ ['title'=>..,'icon'=>..,'route'=>..,'section'=>..,'children'=>[...]], ... ]
     */
    public function treeFor(?User $user): array
    {
        if (!$user) {
            return [];
        }

        // Fresh project / `menus:sync` not run yet -> Super Admin still gets the sidebar from config
        if ($user->isSuperAdmin() && !Menu::exists()) {
            return $this->fromConfig(config('menus', []));
        }

        $query = Menu::query()->where('status', 1)->orderBy('order');

        if (!$user->isSuperAdmin()) {
            $roleId = $user->role_id ?: $user->roles->first()?->id;
            $allowed = DB::table('role_menu')
                ->where('role_id', $roleId)
                ->where('can_view', 1)
                ->pluck('menu_id')
                ->all();

            $query->whereIn('id', $allowed);
        }

        $menus = $query->get();
        $byParent = $menus->groupBy(fn ($m) => $m->parent_id ?? 0);

        $build = function ($parentId) use (&$build, $byParent) {
            return $byParent->get($parentId ?? 0, collect())->map(function ($m) use (&$build) {
                return [
                    'key'      => $m->menu_key,
                    'title'    => $m->title,
                    'icon'     => $m->icon,
                    'route'    => $m->route,
                    'section'  => $m->section,
                    'children' => $build($m->id),
                ];
            })->values()->all();
        };

        // parents of every ticked menu are stored in role_menu automatically (Menu Access screen does this)
        return $build(null);
    }

    protected function fromConfig(array $nodes): array
    {
        return array_map(fn ($n) => [
            'key'      => $n['key'] ?? null,
            'title'    => $n['title'],
            'icon'     => $n['icon'] ?? null,
            'route'    => $n['route'] ?? '#',
            'section'  => $n['section'] ?? null,
            'children' => $this->fromConfig($n['children'] ?? []),
        ], $nodes);
    }

    /* --------------------------------------------------------------
     |  Blade helpers
     * ------------------------------------------------------------ */

    /** Route name -> URL ('#' / unknown routes stay '#'). */
    public static function url(?string $route): string
    {
        if (!$route || $route === '#') {
            return '#';
        }

        if (\Illuminate\Support\Facades\Route::has($route)) {
            return route($route);
        }

        return str_starts_with($route, '/') ? url($route) : '#';
    }

    /** True when this menu (or any child) is the page currently open. */
    public static function isActive(array $node): bool
    {
        $route = $node['route'] ?? null;
        if ($route && $route !== '#' && \Illuminate\Support\Facades\Route::has($route)) {
            if (request()->routeIs($route)) {
                return true;
            }
            // edit / view / sub pages of the same menu keep the sidebar item highlighted
            $current = request()->route()?->getName();
            if (isset(self::ROUTE_FAMILIES[$route]) && $current && self::covers($route, $current)) {
                return true;
            }
        }

        foreach ($node['children'] ?? [] as $child) {
            if (self::isActive($child)) {
                return true;
            }
        }

        return false;
    }
}

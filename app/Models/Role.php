<?php

namespace App\Models;

use Illuminate\Support\Str;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    public const SUPER_ADMIN = 'Super Admin';
    public const EMPLOYEE = 'Employee';

    /** Roles that ship with the system and can't be renamed / deleted. */
    public const SYSTEM_ROLES = [self::SUPER_ADMIN, self::EMPLOYEE];

    /**
     * URL slugs that can never be used as a role login panel
     * (they are real routes / folders of the application).
     */
    public const RESERVED_SLUGS = [
        'employee', 'login', 'logout', 'home', 'dashboard', 'admin', 'api', 'master', 'masters',
        'settings', 'attendance', 'recruitment', 'storage', 'jobs', 'job-apply', 'districts',
        'skills', 'parse-resume', 'map-role-permission', 'super-admin', 'up',
    ];

    protected static function booted(): void
    {
        $forget = fn () => \Illuminate\Support\Facades\Cache::forget(\App\Http\Middleware\StripRolePrefix::CACHE_KEY);
        static::saved($forget);
        static::deleted($forget);
    }

    public function menus()
    {
        return $this->belongsToMany(\App\Models\Menu::class, 'role_menu', 'role_id', 'menu_id')
            ->withPivot('can_view', 'can_add', 'can_edit', 'can_delete')
            ->withTimestamps();
    }

    public function isSystem(): bool
    {
        return in_array($this->name, self::SYSTEM_ROLES, true);
    }

    public function isSuperAdminRole(): bool
    {
        return $this->name === self::SUPER_ADMIN;
    }

    public function isEmployeeRole(): bool
    {
        return $this->name === self::EMPLOYEE;
    }

    /** e.g. "Account Manager" => "account-manager" */
    public static function slugFor(string $name): string
    {
        return Str::slug($name);
    }

    public function loginSlug(): string
    {
        return self::slugFor($this->name);
    }

    /** Login page of this role: Super Admin => "/", Employee => "/employee/login", others => "/{slug}/login" */
    public function loginUrl(): string
    {
        if ($this->isSuperAdminRole()) {
            return url('/');
        }
        if ($this->isEmployeeRole()) {
            return url('/employee/login');
        }

        return url('/' . $this->loginSlug() . '/login');
    }

    public static function findByLoginSlug(string $slug): ?self
    {
        return self::query()->get()->first(fn (self $role) => $role->loginSlug() === $slug);
    }
}

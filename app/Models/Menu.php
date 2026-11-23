<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'menu_key',
        'section',
        'title',
        'icon',
        'route',
        'parent_id',
        'permission_id',
        'order',
        'status',
    ];

    public function getRouteNameAttribute()
    {
        return $this->route;
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order', 'asc');
    }

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function permission()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Permission::class, 'permission_id');
    }

    public function roles()
    {
        return $this->belongsToMany(\App\Models\Role::class, 'role_menu', 'menu_id', 'role_id')
            ->withPivot('can_view', 'can_add', 'can_edit', 'can_delete')
            ->withTimestamps();
    }

    /** Route name -> readable URL path (e.g. "/masters/organisation/company") for the access screen. */
    public static function pathOf(?string $routeName): ?string
    {
        if (!$routeName || $routeName === '#' || !\Illuminate\Support\Facades\Route::has($routeName)) {
            return null;
        }
        try {
            return '/' . ltrim(route($routeName, [], false), '/');
        } catch (\Throwable $e) {
            return null;
        }
    }
}

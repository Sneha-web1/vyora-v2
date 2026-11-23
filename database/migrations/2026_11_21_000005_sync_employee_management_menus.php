<?php

use App\Support\MenuRegistry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('menus') || !Schema::hasColumn('menus', 'menu_key')) {
            return;
        }

        try {
            app(MenuRegistry::class)->sync(true); // prune: old "Resignation" placeholder is removed
            Cache::forget('menus.config.hash');
        } catch (\Throwable $e) {
            report($e); // run `php artisan menus:sync --prune` manually if this fails
        }
    }

    public function down(): void
    {
        //
    }
};

<?php

namespace Database\Seeders;

use App\Support\MenuRegistry;
use Illuminate\Database\Seeder;

/**
 * Menus live in config/menus.php. This seeder just pushes them to the DB
 * (same as running:  php artisan menus:sync)
 */
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        app(MenuRegistry::class)->sync();
    }
}

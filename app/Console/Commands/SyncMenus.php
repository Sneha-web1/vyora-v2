<?php

namespace App\Console\Commands;

use App\Support\MenuRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SyncMenus extends Command
{
    protected $signature = 'menus:sync
                            {--prune : Also delete menus from the database that no longer exist in config/menus.php}
                            {--fresh : Wipe the menus + role_menu tables first, then re-create everything}';

    protected $description = 'Push the menus defined in config/menus.php into the database (menus table) and give them to Super Admin';

    public function handle(MenuRegistry $registry): int
    {
        if (!Schema::hasTable('menus') || !Schema::hasColumn('menus', 'menu_key')) {
            $this->error('The menus table is not ready. Run:  php artisan migrate');

            return self::FAILURE;
        }

        if ($this->option('fresh') && !$this->confirm('--fresh will DELETE every row of menus and role_menu (role -> menu mapping is lost). Continue?', false)) {
            $this->warn('Cancelled.');

            return self::SUCCESS;
        }

        try {
            $result = $registry->sync((bool) $this->option('prune'), (bool) $this->option('fresh'));
        } catch (\Throwable $e) {
            $this->error('Menu sync failed: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info('Menus synced from config/menus.php');
        $this->table(['Created', 'Updated', 'Removed', 'Total in config'], [[
            $result['created'], $result['updated'], $result['removed'], $result['total'],
        ]]);
        $this->line('Super Admin now has access to every menu. Give menus to other roles from: User Management > Menu Access');

        return self::SUCCESS;
    }
}

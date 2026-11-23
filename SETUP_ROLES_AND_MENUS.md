# Vyora – Roles, Role Login Panels & Menu Sync  (setup guide)

## 1. Apply the changes
1. Zip ke andar ki files apne project (`vyora-v1-main/`) me **same path par overwrite** karo.
2. In purani files **delete** kar do (ab use nahi hoti):
   - `app/Http/Controllers/permissionCOntroller.php`  (naya `PermissionController.php` aa gaya)
   - `database/seeders/RoleMenuSeeder.php`
   - `database/seeders/PermissionRoleSeeder.php`
3. Terminal me (project folder me):

```bash
composer dump-autoload
php artisan migrate          # menu_key/section column + Admin & HR role/users hat jayenge
php artisan menus:sync       # config/menus.php  ->  DB (menus table)
php artisan optimize:clear
php artisan permission:cache-reset
```

Fresh DB par: `php artisan migrate:fresh --seed` (roles, super admin, menus sab ban jayenge).

## 2. Ab roles
Sirf 2 built-in roles: **Super Admin** aur **Employee** (dono rename/delete nahi ho sakte).
Admin / HR role + unke demo logins (`admin@example.com`, `hr@example.com`) migration se delete ho jate hain.
Leave / Onboarding ke emails ab Super Admin(s) ko jate hain.

## 3. Login panels
| Role | Login URL |
|---|---|
| Super Admin | `http://localhost:8000/` |
| Employee | `http://localhost:8000/employee/login` |
| Koi bhi naya role (e.g. "Account Manager") | `http://localhost:8000/account-manager/login` |

- Role banate hi uska login panel ban jata hai (role list me "Login Panel" column me URL + copy button).
- Us panel par sirf wahi users login kar sakte hain jinka wahi role ho.
- Naye role ke users: **User Management → Users → Add**. Role ko menus: **User Management → Menu Access**.

## 4. Menu code se add karna  ->  DB me daalna
Menu `config/menus.php` me add karo, phir:

```bash
php artisan menus:sync            # naye add + changed update (safe, kitni bhi baar chalao)
php artisan menus:sync --prune    # jo menu config se hata diye, DB se bhi hata do
php artisan menus:sync --fresh    # menus + role_menu pura wipe karke dobara (mapping lost!)
```

Example (`config/menus.php` me):

```php
[
    'key' => 'training', 'title' => 'Training', 'icon' => 'ti ti-school', 'route' => '#', 'section' => 'HRM',
    'children' => [
        ['key' => 'training.list', 'title' => 'Training List', 'route' => 'training.index'],
    ],
],
```
- `key` unique hona chahiye aur sync ke baad badalna nahi (isi se DB row pehchani jati hai).
- Super Admin ko har naya menu automatically mil jata hai; baaki roles ko Menu Access page se do.

## 5. Security (server-side)
- Roles / Permissions / Menu Access / Users pages sirf Super Admin ke liye (`superadmin.access`).
- Custom role sirf apne menus ke pages khol sakta hai (`menu.access` middleware, route-name prefix se match hota hai).
  Jo route kisi menu ka hissa nahi (ajax/dropdown helpers) wo sabke liye khule rehte hain.

---

## 6. NEW: Roles & Permissions (single page)  — sidebar me **Masters ke theek baad**
URL: `/roles-permissions`  (Super Admin only)

Ek hi page, 3 steps: **1. Role → 2. User → 3. Menu access** (View / Add / Edit / Delete).

```bash
php artisan migrate            # role_menu me can_add + can_delete aate hain
php artisan optimize:clear
```

- **Delete/Edit/Add nahi diya?** Us role ke panel me wo icon/button hide ho jata hai **aur** server bhi request block karta hai (403).
  Kisi custom button ko khud control karna ho to uspe `data-perm="add|edit|delete"` laga do.
- **Naya menu**: `config/menus.php` me add karo — Roles & Permissions page kholte hi auto-scan ho jata hai
  (ya page ke top-right "Scan new menus" button). Naya menu kisi role ko tab tak nahi milta jab tak tick karke Save na karo.
- Purane pages (Roles / Permissions / Menu Access / Users) sidebar se hata diye gaye hain; unke routes abhi bhi code me hain.

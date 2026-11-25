<?php

namespace App\Services;

use App\Mail\LoginOtpMail;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Email-OTP login for every panel:
 *   superadmin -> users table, Super Admin role only
 *   role       -> users table, must belong to the role of /{role-slug}/login
 *   employee   -> employees table, identified by registered email
 *
 * The OTP is mailed to the account's registered email. Only an HMAC of the OTP is cached
 * (5 min, max 5 wrong tries, 60 s resend cooldown).
 */
class OtpLoginService
{
    public const TTL = 300;
    public const COOLDOWN = 60;
    public const MAX_ATTEMPTS = 5;

    /** @return array{user:mixed,guard:string,email:string,name:string,key:string}|null */
    public function findAccount(string $context, string $identifier, ?string $roleSlug = null): ?array
    {
        $identifier = trim($identifier);
        if ($identifier === '') {
            return null;
        }

        // OTP login is email-only: the code goes to the email that is registered on the account
        if (!filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        if ($context === 'employee') {
            $emp = Employee::where('employee_email_bidx', Employee::blindIndex($identifier, 'email') ?? '__none__')->first();

            if (!$emp || !$emp->isActive() || !filter_var($emp->employee_email, FILTER_VALIDATE_EMAIL)) {
                return null;
            }

            return [
                'user'  => $emp,
                'guard' => 'employee',
                'email' => $emp->employee_email,
                'name'  => $emp->employee_name,
                'key'   => 'employee:' . $emp->employee_id,
            ];
        }

        $user = User::where('email', $identifier)->first();
        if (!$user || (int) $user->status !== 1) {
            return null;
        }

        if ($context === 'superadmin') {
            if (!$user->isSuperAdmin()) {
                return null;
            }
            $key = 'superadmin:' . $user->id;
        } elseif ($context === 'role') {
            $role = $roleSlug ? Role::findByLoginSlug($roleSlug) : null;
            if (!$role || $role->isSystem()
                || !((int) $user->role_id === (int) $role->id || $user->hasRole($role->name))) {
                return null;
            }
            $key = 'role:' . $role->id . ':' . $user->id;
        } else {
            return null;
        }

        return [
            'user'  => $user,
            'guard' => 'web',
            'email' => $user->email,
            'name'  => $user->name,
            'key'   => $key,
        ];
    }

    /**
     * Generate + mail an OTP. Does nothing while the resend cooldown is running.
     * Returns 'sent' | 'cooldown' | 'mail_failed: <reason>' (the reason is only shown to the user when APP_DEBUG=true).
     */
    public function send(array $account): string
    {
        $k = $account['key'];

        if (!Cache::add("otp_cd:$k", 1, self::COOLDOWN)) {
            Log::info("Login OTP: resend cooldown active for $k - no new OTP sent");
            return 'cooldown';
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put("otp:$k", ['h' => $this->hash($otp), 'n' => 0], self::TTL);

        if (app()->environment('local') && config('app.debug')) {
            Log::debug("DEV ONLY - login OTP for {$account['email']} is $otp");
        }

        try {
            Mail::to($account['email'])->send(new LoginOtpMail($account['name'], $otp, (int) (self::TTL / 60)));
        } catch (\Throwable $e) {
            Cache::forget("otp:$k");
            Cache::forget("otp_cd:$k");
            Log::error('Login OTP mail failed: ' . $e->getMessage());
            return 'mail_failed: ' . $e->getMessage();
        }

        Log::info("Login OTP mailed to account $k");
        return 'sent';
    }

    public function verify(array $account, string $otp): bool
    {
        $k = $account['key'];
        $data = Cache::get("otp:$k");

        if (!$data) {
            return false;
        }

        if ($data['n'] >= self::MAX_ATTEMPTS) {
            Cache::forget("otp:$k");
            return false;
        }

        if (!hash_equals($data['h'], $this->hash($otp))) {
            $data['n']++;
            Cache::put("otp:$k", $data, self::TTL);
            return false;
        }

        Cache::forget("otp:$k");
        Cache::forget("otp_cd:$k");

        return true;
    }

    private function hash(string $otp): string
    {
        return hash_hmac('sha256', $otp, (string) config('app.key'));
    }
}

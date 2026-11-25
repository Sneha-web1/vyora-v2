<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Employee extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $table = "employees";
    protected $primaryKey = "employee_id";
    public $timestamps = false; // table keeps created_at / updated_at but they are not auto-managed

    protected $fillable = [
        "patId",
        "employee_name",
        "employee_email",
        "employee_mobile",
        "employee_password",
        "login_pin",
        "is_pin_enabled",
        "pin_set_at",
        "last_login_at",
        "employee_gender",
        "blood_group",
        "posting_country",
        "posting_state",
        "posting_city",
        "joining_date",
        "date_of_confirmation",
        "employment_type",
        "designation_id",
        "agency_id",
        "shift_id",
        "week_off_days",
        "supervisor_id",
        "is_supervisor",
        "address",
        "notice_period_days",
        "dpdp_privacy_notice_ack",
        "dpdp_consent_given",
        "dpdp_sharing_consent",
        "dpdp_consent_mode",
        "dpdp_consent_at",
        "dpdp_consent_version",
        "dpdp_consent_ip",
        "dpdp_consent_recorded_by",
        "status",
        "profile_image",
    ];

    protected $casts = [
        // DPDP: PII is stored AES-256 encrypted (APP_KEY). Read/write stays transparent.
        "employee_email"  => "encrypted",
        "employee_mobile" => "encrypted",
        "is_supervisor"  => "boolean",
        "is_pin_enabled" => "boolean",
        "pin_set_at"     => "datetime",
        "last_login_at"  => "datetime",
        "week_off_days"  => "array",
        "notice_period_days"      => "integer",
        "dpdp_privacy_notice_ack" => "boolean",
        "dpdp_consent_given"      => "boolean",
        "dpdp_sharing_consent"    => "boolean",
        "dpdp_consent_at"         => "datetime",
    ];

    protected $hidden = [
        "employee_password",
        "login_pin",
        "employee_email_bidx",
        "employee_mobile_bidx",
    ];

    // tell Laravel to use employee_password instead of "password"
    public function getAuthPassword()
    {
        return $this->employee_password;
    }


    /*
    |--------------------------------------------------------------------------
    | DPDP: encrypted PII + blind index
    |--------------------------------------------------------------------------
    | employee_email / employee_mobile are encrypted, so SQL cannot compare or
    | LIKE them. Alongside each one we keep a keyed HMAC-SHA256 "blind index"
    | (employee_email_bidx / employee_mobile_bidx). It allows exact-match lookup
    | (login, unique check, search) without storing the plain value, and cannot
    | be reversed without PII_INDEX_KEY.
    */
    protected static function booted(): void
    {
        static::saving(function (Employee $e) {
            if ($e->isDirty('employee_email') || empty($e->employee_email_bidx)) {
                $e->employee_email_bidx = self::blindIndex($e->employee_email, 'email');
            }
            if ($e->isDirty('employee_mobile') || empty($e->employee_mobile_bidx)) {
                $e->employee_mobile_bidx = self::blindIndex($e->employee_mobile, 'mobile');
            }
        });
    }

    /** Normalise then HMAC. Returns null for empty input. */
    public static function blindIndex(?string $value, string $type): ?string
    {
        $value = self::normalizePii($value, $type);
        if ($value === '') {
            return null;
        }

        $key = config('app.pii_index_key') ?: hash('sha256', 'pii-index|' . config('app.key'));

        return hash_hmac('sha256', $type . '|' . $value, $key);
    }

    public static function normalizePii(?string $value, string $type): string
    {
        $value = trim((string) $value);

        if ($type === 'email') {
            return mb_strtolower($value);
        }

        // mobile: keep digits only and compare on the last 10 (ignores +91 / 0 / spaces)
        $digits = preg_replace('/\D+/', '', $value);

        return strlen($digits) > 10 ? substr($digits, -10) : $digits;
    }

    /** s****@gmail.com  (display only - never mask values that are saved or mailed) */
    public static function maskEmail(?string $email): ?string
    {
        if ($email === null || trim($email) === '' || !str_contains($email, '@')) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);

        return mb_substr($local, 0, 1) . '****@' . $domain;
    }

    /** ******3210 */
    public static function maskMobile(?string $mobile): ?string
    {
        if ($mobile === null || trim($mobile) === '') {
            return $mobile;
        }

        $digits = substr(preg_replace('/\D+/', '', $mobile), -10);

        return str_repeat('*', max(strlen($digits) - 4, 4)) . substr($digits, -4);
    }

    public function getMaskedEmailAttribute(): ?string
    {
        return self::maskEmail($this->employee_email);
    }

    public function getMaskedMobileAttribute(): ?string
    {
        return self::maskMobile($this->employee_mobile);
    }

    public function scopeWhereEmailIs($query, ?string $email)
    {
        return $query->where('employee_email_bidx', self::blindIndex($email, 'email') ?? '__none__');
    }

    public function scopeWhereMobileIs($query, ?string $mobile)
    {
        return $query->where('employee_mobile_bidx', self::blindIndex($mobile, 'mobile') ?? '__none__');
    }

    /** Use inside a where(function ($q) {...}) group: exact email OR exact mobile match. */
    public function scopeOrWhereEmployeeContact($query, ?string $term)
    {
        $email  = self::blindIndex($term, 'email');
        $mobile = strlen(self::normalizePii($term, 'mobile')) >= 10 ? self::blindIndex($term, 'mobile') : null;

        return $query->orWhere(function ($q) use ($email, $mobile) {
            $q->whereRaw('1 = 0');
            if ($email)  { $q->orWhere('employee_email_bidx', $email); }
            if ($mobile) { $q->orWhere('employee_mobile_bidx', $mobile); }
        });
    }

    /** Active = not soft-deleted and status not Inactive-like. */
    public function isActive(): bool
    {
        if ($this->deleted_at !== null) {
            return false;
        }

        $status = strtolower(trim((string) $this->status));

        return !in_array($status, ["inactive", "0", "disabled", "suspended", "left", "terminated"], true);
    }

    public function deactivate(): void
    {
        $this->status = "Inactive";
        $this->save();
    }

    public function activate(): void
    {
        $this->status = "Active";
        $this->save();
    }

    public function scopeActive($query)
    {
        return $query->whereNull("deleted_at")
            ->where(function ($q) {
                $q->where("status", "Active")
                    ->orWhere("status", "1")
                    ->orWhereNull("status")
                    ->orWhere("status", "");
            })
            ->where("status", "!=", "Inactive");
    }

    public function isSupervisor(): bool
    {
        return (bool) $this->is_supervisor || self::where("supervisor_id", $this->employee_id)->exists();
    }

    public function subordinates()
    {
        return $this->hasMany(Employee::class, "supervisor_id", "employee_id");
    }

    public function supervisorEmployee()
    {
        return $this->belongsTo(Employee::class, "supervisor_id", "employee_id");
    }

    public function country()
    {
        return $this->belongsTo(Country::class, "posting_country", "id");
    }

    public function state()
    {
        return $this->belongsTo(CountryState::class, "posting_state", "id");
    }

    public function city()
    {
        return $this->belongsTo(StateCity::class, "posting_city", "id");
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class, "designation_id", "id")
            ->withDefault([
                "name" => "Staff Member",
            ]);
    }

    public function agency()
    {
        return $this->belongsTo(Agency::class, "agency_id", "id");
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, "shift_id", "id");
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class, "employee_id", "employee_id")
            ->orderBy("created_at", "desc");
    }

    public function familyMembers()
    {
        return $this->hasMany(EmployeeFamilyMember::class, "employee_id", "employee_id")
            ->orderBy("id");
    }

    public function resignations()
    {
        return $this->hasMany(EmployeeResignation::class, "employee_id", "employee_id")
            ->orderBy("id", "desc");
    }

    public function latestResignation()
    {
        return $this->hasOne(EmployeeResignation::class, "employee_id", "employee_id")->latestOfMany();
    }

    public function activeResignation()
    {
        return $this->hasOne(EmployeeResignation::class, "employee_id", "employee_id")
            ->whereIn("status", ["pending", "in_review", "approved"])
            ->latestOfMany();
    }
}

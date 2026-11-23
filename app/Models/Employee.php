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
        "status",
        "profile_image",
    ];

    protected $casts = [
        "is_supervisor"  => "boolean",
        "is_pin_enabled" => "boolean",
        "pin_set_at"     => "datetime",
        "last_login_at"  => "datetime",
        "week_off_days"  => "array",
    ];

    protected $hidden = [
        "employee_password",
        "login_pin",
    ];

    // tell Laravel to use employee_password instead of "password"
    public function getAuthPassword()
    {
        return $this->employee_password;
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

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Company;
class User extends Authenticatable
{
    use HasRoles;
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    public function profile()
    {
        return $this->hasOne(EmployeeProfile::class, 'user_id');
    }

    public function role_id()
    {
        return $this->roles->first()?->id;
    }
    /** The role row referenced by users.role_id */
    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->roleModel?->name === Role::SUPER_ADMIN || $this->hasRole(Role::SUPER_ADMIN);
    }

    /** Emails of every active Super Admin (used for system notifications). */
    public static function superAdminEmails(): array
    {
        $roleId = Role::where('name', Role::SUPER_ADMIN)->value('id');

        return $roleId
            ? static::where('role_id', $roleId)->where('status', 1)->pluck('email')->all()
            : [];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role_id',
        'status',
        'profile_photo',
        'address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}

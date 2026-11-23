<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Policy extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'policies';

    protected $fillable = [
        'policy_title',
        'policy_code',
        'company_id',
        'branch_id',
        'department_id',
        'effective_from',
        'expiry_date',
        'policy_document',
        'status',
        'description',
    ];

    protected $casts = [
        'effective_from' => 'date:Y-m-d',
        'expiry_date' => 'date:Y-m-d',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}

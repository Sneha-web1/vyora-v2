<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'category_id',
        'department_name',
        'department_code',
        'department_head',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function category()
{
    return $this->belongsTo(Category::class, 'category_id');
}

}

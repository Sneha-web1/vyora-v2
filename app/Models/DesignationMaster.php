<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DesignationMaster extends Model
{
    use HasFactory;

    protected $table = 'designationmaster';
    protected $primaryKey = 'DesignationID';
    public $timestamps = false;

    protected $fillable = [
        'Designation',
        'CreationDate',
        'company_id',
        'branch_id',
        'department_id',
    ];

    /**
     * Accessor for 'name' to keep backwards-compatibility
     */
    public function getNameAttribute()
    {
        return $this->attributes['Designation'] ?? '';
    }

    /**
     * Accessor for 'id' to keep backwards-compatibility
     */
    public function getIdAttribute()
    {
        return $this->attributes['DesignationID'] ?? null;
    }

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

    public function employees()
    {
        return $this->hasMany(Employee::class, 'designation_id', 'DesignationID');
    }
}

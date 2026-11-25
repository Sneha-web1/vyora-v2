<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeFamilyMember extends Model
{
    use SoftDeletes;

    protected $table = 'employee_family_members';

    public const RELATIONS = [
        'Father', 'Mother', 'Spouse', 'Son', 'Daughter', 'Brother', 'Sister', 'Guardian', 'Other',
    ];

    protected $fillable = [
        'employee_id',
        'name',
        'phone',
        'relation',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}

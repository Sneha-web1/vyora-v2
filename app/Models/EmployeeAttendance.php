<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeAttendance extends Model
{
    protected $table = 'employee_attendances';

    protected $fillable = [
        'employee_id',
        'attendance_date',
        'check_in_at',
        'check_out_at',
        'check_in_ip',
        'check_out_ip',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
        'status',
        'note',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_at'     => 'datetime',
        'check_out_at'    => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}

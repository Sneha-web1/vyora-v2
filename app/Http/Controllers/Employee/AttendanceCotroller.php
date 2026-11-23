<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Attendance\AttendanceRegisterReportController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AttendanceCotroller extends Controller
{
    /**
     * Display the logged-in employee's own Attendance Register.
     */
    public function index(Request $request, AttendanceRegisterReportController $registerReport)
    {
        $employeeId = auth('employee')->id();

        $data = $registerReport->buildRegisterData($request, $employeeId);
        $data['title'] = 'My Attendance Register';

        return view('employee.attendance.index', $data);
    }
}

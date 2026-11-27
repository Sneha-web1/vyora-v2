<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckinController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RoleAuthController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Employee\EmployeeCotroller;
use App\Http\Controllers\Admin\Clients\ClientsController;
use App\Http\Controllers\Hr\HumanResourceController;
use App\Http\Controllers\Hr\JobCategoryController;
use App\Http\Controllers\Hr\hrEmployeeController;
use App\Http\Controllers\Hr\hrEmployeeLeaveController;
use App\Http\Controllers\Hr\LeaveSettingController;
use App\Http\Controllers\Hr\AttendanceController;
use App\Http\Controllers\Hr\PerformanceController;
use App\Http\Controllers\Hr\PerformanceReviewController;
use App\Http\Controllers\Hr\PerformanceAppraisalController;
use App\Http\Controllers\Hr\GoalTrackingController;
use App\Http\Controllers\Hr\TraningController;
use App\Http\Controllers\Hr\TrainersController;
use App\Http\Controllers\Hr\TraningTypeController;
use App\Http\Controllers\Hr\MasterController;
use App\Http\Controllers\StateCityController;
use App\Http\Controllers\SkillsController;
use App\Http\Controllers\MapRolePermission;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AccessController;
use App\Http\Controllers\Employee\leavesController;
use App\Http\Controllers\Employee\AttendanceCotroller;
use App\Http\Controllers\Employee\PerformanceCotroller;
use App\Http\Controllers\Employee\PerformanceReviewCotroller;
use App\Http\Controllers\Employee\PayslipCotroller;
use App\Http\Controllers\AdminHR\DashboardController;
use App\Http\Controllers\Masters\AgencyController;
use App\Http\Controllers\Masters\CompanyController;
use App\Http\Controllers\Masters\BranchController;
use App\Http\Controllers\Masters\DepartmentController;
use App\Http\Controllers\Masters\DesignationController;
use App\Http\Controllers\Masters\ShiftController;
use App\Http\Controllers\Masters\LeaveTypeController;
use App\Http\Controllers\Masters\HolidayController;
use App\Http\Controllers\Masters\PolicyController;
use App\Http\Controllers\Masters\CategoryController;
use App\Http\Controllers\Masters\SalaryComponentController;
use App\Http\Controllers\Masters\SalaryStructureController;
use App\Http\Controllers\Masters\CalculationRulesController;
use App\Http\Controllers\Masters\TaxationController;
use App\Http\Controllers\Masters\ApplicabilityController;
use App\Http\Controllers\Masters\PaymentFrequencyController;
use App\Http\Controllers\Masters\PaymentModeController;
use App\Http\Controllers\Masters\VisibilityControlController;
use App\Http\Controllers\Masters\AdminEmployeeController;
use App\Http\Controllers\Admin\Admin_EmployeesController;
use App\Http\Controllers\Admin\AdminResignationController;
use App\Http\Controllers\Admin\EmployeeReportController;
use App\Http\Controllers\AdminHR\DocumentController;
use App\Http\Controllers\Masters\JobsController;
use App\Http\Controllers\Attendance\AttendanceRegisterReportController;
use App\Http\Controllers\Attendance\AttendanceOrgReportController;
use App\Http\Controllers\Attendance\ConsistentAttendeesController;
use App\Http\Controllers\EmployeeAuthController;
use App\Http\Controllers\Employee\EmployeeController;
use App\Http\Controllers\Hr\JobsController as HrJobsController;
use App\Http\Controllers\Masters\JafController;
use App\Http\Controllers\Masters\LeaveMappingController;
use App\Http\Controllers\Masters\JobsController as MastersJobsController;
use App\Http\Controllers\Masters\SkillController;
use App\Http\Controllers\Masters\OnboardingController;
use App\Http\Controllers\PMT\FeedbackController;
use App\Http\Controllers\PMT\ReviewEmployeeController;
use App\Http\Controllers\PMT\SelfAppraisalController;
use App\Http\Controllers\PMT\ViewFeedbackController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\AppliedController;
use App\Http\Controllers\Api\JobApplicationController;


Route::get('/districts/search', [StateCityController::class, 'search'])->name('districts.search');
Route::get('/skills/search', [SkillsController::class, 'skillsSearch'])->name('skills.search');

Route::post('/parse-resume', [ResumeController::class, 'parse'])->name('resume.parse');
Route::post('/job-apply', [JobApplicationController::class, 'store'])->name('job.application.store');


Route::middleware(['auth', 'menu.access'])->group(function () {

    // Dashboard
    Route::get('/get-departments/{category_id}', [DashboardController::class, 'getDepartments']);
    Route::get('/get-designation/{department_id}', [DashboardController::class, 'getDesignation']);
    Route::get('/get-state/{country_id}', [DashboardController::class, 'getStates'])->name('getStates');
    Route::get('/get-cities/{state_id}', [DashboardController::class, 'getCities'])->name('getCities');
    Route::get('/leave-type/get-days/{id}', [LeaveMappingController::class, 'getDays']);


    Route::get('/home', [DashboardController::class, 'index'])->name('home');
    // HR Dashboard
    Route::get('/dashboard', [HumanResourceController::class, 'index'])->name('dashboard');
    // Job Categories
    // Master/Organisation/Company
    Route::get('master/organization', [CompanyController::class, 'index'])->name('masters.organisation.company');
    Route::get('master/organization/create', [CompanyController::class, 'create'])->name('masters.organisation.company.create');
    Route::post('master/organization/store', [CompanyController::class, 'store'])->name('masters.organisation.company.store');
    Route::get('master/organization/list', [CompanyController::class, 'list'])->name('masters.organisation.company.list');
    Route::get('master/organization/view/{id}', [CompanyController::class, 'show'])->name('masters.organisation.company.view');
    Route::get('master/organization/edit/{id}', [CompanyController::class, 'edit'])->name('masters.organisation.company.edit');
    Route::put('master/organization/update/{id}', [CompanyController::class, 'update'])->name('masters.organisation.company.update');
    Route::delete('master/organization/delete/{id}', [CompanyController::class, 'destroy'])->name('masters.organisation.company.delete');

    // Master/Organisation/Branch
    Route::get('master/branch', [BranchController::class, 'index'])->name('masters.organisation.branch');
    Route::get('master/branch/create', [BranchController::class, 'create'])->name('masters.organisation.branch.create');
    Route::post('master/branch/store', [BranchController::class, 'store'])->name('masters.organisation.branch.store');
    Route::get('master/branch/list', [BranchController::class, 'list'])->name('masters.organisation.branch.list');
    Route::get('master/branch/by-company/{company}', [BranchController::class, 'byCompany'])->name('masters.organisation.branch.by-company');
    Route::get('master/branch/edit/{id}', [BranchController::class, 'edit'])->name('masters.organisation.branch.edit');
    Route::put('master/branch/update/{id}', [BranchController::class, 'update'])->name('masters.organisation.branch.update');
    Route::delete('master/branch/delete/{id}', [BranchController::class, 'destroy'])->name('masters.organisation.branch.delete');

    // Master/Organisation/Department
    Route::get('master/department', [DepartmentController::class, 'index'])->name('settings.department');
    Route::get('master/department/create', [DepartmentController::class, 'create'])->name('settings.department.create');
    Route::post('master/department/store', [DepartmentController::class, 'store'])->name('settings.department.store');
    Route::get('master/department/edit/{id}', [DepartmentController::class, 'edit'])->name('settings.department.edit');
    Route::put('master/department/update/{id}', [DepartmentController::class, 'update'])->name('settings.department.update');
    Route::delete('master/department/delete/{id}', [DepartmentController::class, 'destroy'])->name('settings.department.destroy');
    Route::get('master/department/list', [DepartmentController::class, 'list'])->name('settings.department.list');
    Route::get('master/department/by-company/{company}', [DepartmentController::class, 'byCompany'])->name('settings.department.by-company');

    // Master/Organisation/Job Skill
    Route::get('master/skill', [SkillController::class, 'index'])->name('settings.skills');
    Route::get('master/skill/list', [SkillController::class, 'list'])->name('settings.skill.list');
    Route::get('master/skill/create', [SkillController::class, 'create'])->name('settings.skill.create');
    Route::post('master/skill/store', [SkillController::class, 'store'])->name('settings.skills.store');
    Route::get('master/skill/edit/{id}', [SkillController::class, 'edit'])->name('settings.skill.edit');
    Route::put('master/skill/update/{id}', [SkillController::class, 'update'])->name('settings.skill.update');
    Route::delete('master/skill/delete/{id}', [SkillController::class, 'destroy'])->name('settings.skill.destroy');

    // Designation Master
    Route::get('master/designation', [DesignationController::class, 'index'])->name('masters.organisation.designation');
    Route::get('master/designation/list', [DesignationController::class, 'list'])->name('masters.organisation.designation.list');
    Route::get('master/designation/view/{id}', [DesignationController::class, 'show'])->name('masters.organisation.designation.view');
    Route::get('master/designation/create', [DesignationController::class, 'create'])->name('masters.organisation.designation.create');
    Route::post('master/designation/store', [DesignationController::class, 'store'])->name('masters.organisation.designation.store');
    Route::get('master/designation/edit/{id}', [DesignationController::class, 'edit'])->name('masters.organisation.designation.edit');
    Route::match(['post', 'put'], 'master/designation/update/{id}', [DesignationController::class, 'update'])->name('masters.organisation.designation.update');
    Route::match(['delete', 'post'], 'master/designation/delete/{id}', [DesignationController::class, 'destroy'])->name('masters.organisation.designation.destroy');

    // Designation settings aliases

    // Shift Master
    Route::get('master/shift', [ShiftController::class, 'index'])->name('masters.organisation.shift');
    Route::get('master/shift/list', [ShiftController::class, 'list'])->name('masters.organisation.shift.list');
    Route::get('master/shift/create', [ShiftController::class, 'create'])->name('masters.organisation.shift.create');
    Route::post('master/shift/store', [ShiftController::class, 'store'])->name('masters.organisation.shift.store');
    Route::get('master/shift/edit/{id}', [ShiftController::class, 'edit'])->name('masters.organisation.shift.edit');
    Route::match(['post', 'put'], 'master/shift/update/{id}', [ShiftController::class, 'update'])->name('masters.organisation.shift.update');
    Route::delete('master/shift/delete/{id}', [ShiftController::class, 'destroy'])->name('masters.organisation.shift.delete');
    Route::post('master/shift/toggle-status/{id}', [ShiftController::class, 'toggleStatus'])->name('masters.organisation.shift.toggle-status');

    // Shift settings aliases

    Route::prefix('master/agency')->name('masters.organisation.agency')->group(function () {
        Route::get('/', [AgencyController::class, 'index']);
        Route::get('/list', [AgencyController::class, 'list'])->name('.list');
        Route::get('/create', [AgencyController::class, 'create'])->name('.create');
        Route::post('/store', [AgencyController::class, 'store'])->name('.store');
        Route::get('/view/{id}', [AgencyController::class, 'show'])->name('.view');
        Route::get('/edit/{id}', [AgencyController::class, 'edit'])->name('.edit');
        Route::put('/update/{id}', [AgencyController::class, 'update'])->name('.update');
        Route::delete('/delete/{id}', [AgencyController::class, 'destroy'])->name('.delete');
        Route::post('/status/{id}', [AgencyController::class, 'updateStatus'])->name('.status');
        Route::get('/mapping', [AgencyController::class, 'mapping'])->name('.mapping');
        Route::post('/mapping', [AgencyController::class, 'saveMapping'])->name('.mapping.save');
    });

    // Policy / Category / others
    Route::get('master/policy', [PolicyController::class, 'index'])->name('masters.organisation.policy');
    Route::get('master/policy/list', [PolicyController::class, 'list'])->name('masters.organisation.policy.list');
    Route::get('master/policy/view/{id}', [PolicyController::class, 'show'])->name('masters.organisation.policy.view');
    Route::get('master/policy/create', [PolicyController::class, 'create'])->name('masters.organisation.policy.create');
    Route::post('master/policy/store', [PolicyController::class, 'store'])->name('masters.organisation.policy.store');
    Route::get('master/policy/edit/{id}', [PolicyController::class, 'edit'])->name('masters.organisation.policy.edit');
    Route::put('master/policy/update/{id}', [PolicyController::class, 'update'])->name('masters.organisation.policy.update');
    Route::delete('master/policy/delete/{id}', [PolicyController::class, 'destroy'])->name('masters.organisation.policy.delete');
    Route::get('master/category', [CategoryController::class, 'index'])->name('settings.category');
    Route::get('master/category/list', [CategoryController::class, 'list'])->name('settings.category.list');
    Route::get('master/category/create', [CategoryController::class, 'create'])->name('settings.category.create');
    Route::post('master/category/store', [CategoryController::class, 'store'])->name('settings.category.store');
    Route::get('master/category/edit/{id}', [CategoryController::class, 'edit'])->name('settings.category.edit');
    Route::put('master/category/update/{id}', [CategoryController::class, 'update'])->name('settings.category.update');
    Route::delete('master/category/delete/{id}', [CategoryController::class, 'destroy'])->name('settings.category.destroy');

    // Jobs
    // Route::get('jobs', [JobsController::class, 'index'])->name('jobs.index');
    // Route::get('jobs/create', [JobsController::class, 'create'])->name('jobs.create');
    // Route::post('jobs/store', [JobsController::class, 'store'])->name('jobs.store');
    // Route::get('jobs/list', [JobsController::class, 'list'])->name('jobs.list');

    // Master/Organisation/Compney


    // Master/Organisation/Branch




    // Master/Organisation/Department

    // Master/Organisation/Designation

    // Master/Organisation/Shift

    // Master/Organisation/Leave Type
    Route::get('settings/leave-type', [LeaveTypeController::class, 'index'])->name('settings.leave-type');
    Route::get('settings/leave-type/create', [LeaveTypeController::class, 'create'])->name('settings.leave-type.create');
    Route::post('settings/leave-type/store', [LeaveTypeController::class, 'store'])->name('settings.leave-type.store');
    Route::get('settings/leave-type/edit/{id}', [LeaveTypeController::class, 'edit'])->name('settings.leave-type.edit');
    Route::put('settings/leave-type/edit/{id}', [LeaveTypeController::class, 'update'])->name('settings.leave-type.update');
    Route::delete('settings/leave-type/delete/{id}', [LeaveTypeController::class, 'destroy'])->name('settings.leave-type.destroy');
    Route::get('settings/leave-type/list', [LeaveTypeController::class, 'list'])->name('settings.leave-type.list');




    // Master/Organisation/Leave Allow
    Route::get('settings/leave-allow', [LeaveMappingController::class, 'index'])->name('settings.leave-allow');
    Route::get('settings/leave-allow/create', [LeaveMappingController::class, 'create'])->name('settings.leave-allow.create');
    Route::post('settings/leave-mapping/store', [LeaveMappingController::class, 'store'])->name('settings.leave.mapping.store');
    Route::get('settings/leave-mapping/edit/{id}', [LeaveMappingController::class, 'edit'])->name('settings.leave.mapping.edit');
    Route::put('settings/leave-mapping/update/{id}', [LeaveMappingController::class, 'update'])->name('settings.leave.mapping.update');
    Route::get('settings/leave-mapping/list', [LeaveMappingController::class, 'list'])->name('settings.leave.mapping.list');
    Route::delete('settings/leave-mapping/delete/{id}', [LeaveMappingController::class, 'destroy'])->name('settings.leave.mapping.delete');


    // Master/Organisation/Holiday
    Route::get('settings/holiday', [HolidayController::class, 'index'])->name('settings.holiday');
    Route::get('settings/holiday/list', [HolidayController::class, 'list'])->name('settings.holiday.list');
    Route::get('settings/holiday/create', [HolidayController::class, 'create'])->name('settings.holiday.create');
    Route::post('settings/holiday/store', [HolidayController::class, 'store'])->name('settings.holiday.store');
    Route::get('settings/holiday/edit/{id}', [HolidayController::class, 'edit'])->name('settings.holiday.edit');
    Route::put('settings/holiday/update/{id}', [HolidayController::class, 'update'])->name('settings.holiday.update');
    Route::delete('settings/holiday/delete/{id}', [HolidayController::class, 'destroy'])->name('settings.holiday.delete');

    // Master/Organisation/Policy


    // Master/Recruitment/Jobs
    Route::get('employee/details/{id}', [AppliedController::class, 'usersDetails'])->name('employee.details');


    Route::get('recruitment/jobs', [JobsController::class, 'index'])->name('recruitment.jobs');
    Route::get('recruitment/job/applied-candidate', [JobsController::class, 'appliedCandidate'])->name('recruitment.jobs.applied-candidate');
    Route::get('recruitment/jobs/create', [JobsController::class, 'create'])->name('recruitment.jobs.create');
    Route::post('recruitment/jobs/store', [JobsController::class, 'store'])->name('recruitment.jobs.store');
    Route::get('recruitment/jobs/list', [JobsController::class, 'list'])->name('recruitment.jobs.list');
    Route::get('recruitment/jobs/recommended-job', [JobsController::class, 'recommendedJob'])->name('recruitment.jobs.recommended-job');
    Route::post('/recruitment/jobs/status/{id}', [JobsController::class, 'toggleStatus'])->name('recruitment.jobs.toggle-status');
    Route::get('recruitment/jobs/job-listings/{slug}', [JobsController::class, 'jobDetails'])->name('recruitment.jobs.job-deatils');
    Route::get('/recruitment/jobs/counts', [JobsController::class, 'jobCounts'])->name('recruitment.jobs.counts');
    Route::get('/recruitment/jobs/{slug}/apply', [JobsController::class, 'jobForm'])->name('recruitment.jobs.apply.form');
    Route::get('recruitment/jobs/details-ajax', [JobsController::class, 'jobDetailsAjax'])->name('recruitment.jobs.details.ajax');

    Route::get('recruitment/jobs/edit/{id}', [JobsController::class, 'edit'])->name('recruitment.jobs.edit');
    Route::put('recruitment/jobs/update/{id}', [JobsController::class, 'update'])->name('recruitment.jobs.update');
    Route::delete('recruitment/jobs/delete/{id}', [JobsController::class, 'destroy'])->name('recruitment.jobs.delete');

    Route::get('employee/onboarding/{slug}/{id}', [OnboardingController::class, 'index'])->name('employee.onboarding');
    Route::post('/onboarding/{id}/shortlist', [OnboardingController::class, 'shortlist'])->name('onboarding.shortlist');
    Route::post('/onboarding/{id}/interview-schedule', [OnboardingController::class, 'storeInterviewSchedule'])->name('onboarding.interview.schedule.store');
    Route::post('/onboarding/interview-status/{id}', [OnboardingController::class, 'updateInterviewStatus'])->name('onboarding.interview.status.update');
    Route::get('recruitment/jobs/create-job-questionaire', [JafController::class, 'index'])->name('create-job-questionaire');
    Route::post('/onboarding/send-confirmation', [OnboardingController::class, 'sendConfirmation'])->name('onboarding.sendConfirmation');
    Route::patch('/onboarding/update-confirmation-template', [OnboardingController::class, 'updateConfirmationTemplate'])->name('onboarding.updateTemplate');
    Route::get('jaf/list', [JafController::class, 'list'])->name('jaf.list');
    Route::post('recruitment/jobs/create-job-questionaire/store', [JafController::class, 'store'])->name('jaf.store');
    Route::get('recruitment/jobs/create-job-questionaire/show', [JafController::class, 'show'])->name('jaf.index');
    Route::get('recruitment/jobs/create-job-questionaire/edit/{id}', [JafController::class, 'edit'])->name('jaf.edit');
    Route::put('recruitment/jobs/create-job-questionaire/update/{id}', [JafController::class, 'update'])->name('jaf.update');
    Route::delete('recruitment/jobs/create-job-questionaire/delete/{id}', [JafController::class, 'destroy'])->name('jaf.delete');

    // Master/Payroll/salary-component
    Route::get('master/salary-component', [SalaryComponentController::class, 'index'])->name('masters.payroll.salary-component');
    Route::get('master/salary-component/create', [SalaryComponentController::class, 'create'])->name('masters.payroll.salary-component.create');
    Route::post('master/salary-component/store', [SalaryComponentController::class, 'store'])->name('masters.payroll.salary-component.store');

    // Master/Payroll/salary-structure
    Route::get('master/salary-structure', [SalaryStructureController::class, 'index'])->name('masters.payroll.salary-structure');
    Route::get('master/salary-structure/create', [SalaryStructureController::class, 'create'])->name('masters.payroll.salary-structure.create');
    Route::get('master/salary-structure/store', [SalaryStructureController::class, 'store'])->name('masters.payroll.salary-structure.store');

    // Master/Payroll/calculation-rules
    Route::get('master/calculation-rules', [CalculationRulesController::class, 'index'])->name('masters.payroll.calculation-rules');
    Route::get('master/calculation-rules/create', [CalculationRulesController::class, 'create'])->name('masters.payroll.calculation-rules.create');
    Route::get('master/calculation-rules/store', [CalculationRulesController::class, 'store'])->name('masters.payroll.calculation-rules.store');

    // Master/Payroll/Taxation
    Route::get('master/taxation', [TaxationController::class, 'index'])->name('masters.payroll.taxation');
    Route::get('master/taxation/create', [TaxationController::class, 'create'])->name('masters.payroll.taxation.create');
    Route::get('master/taxation/store', [TaxationController::class, 'store'])->name('masters.payroll.taxation.store');

    // Master/Payroll/applicability
    Route::get('master/applicability', [ApplicabilityController::class, 'index'])->name('masters.payroll.applicability');
    Route::get('master/applicability/create', [ApplicabilityController::class, 'create'])->name('masters.payroll.applicability.create');
    Route::get('master/applicability/store', [ApplicabilityController::class, 'store'])->name('masters.payroll.applicability.store');

    // Master/Payroll/PaymentFrequency
    Route::get('master/payment-frequency', [PaymentFrequencyController::class, 'index'])->name('masters.payroll.payment-frequency');
    Route::get('master/payment-frequency/create', [PaymentFrequencyController::class, 'create'])->name('masters.payroll.payment-frequency.create');
    Route::get('master/payment-frequency/store', [PaymentFrequencyController::class, 'store'])->name('masters.payroll.payment-frequency.store');

    // Master/Payroll/payment-mode
    Route::get('master/payment-mode', [PaymentModeController::class, 'index'])->name('masters.payroll.payment-mode');
    Route::get('master/payment-mode/create', [PaymentModeController::class, 'create'])->name('masters.payroll.payment-mode.create');
    Route::get('master/payment-mode/store', [PaymentModeController::class, 'store'])->name('masters.payroll.payment-mode.store');


    // Master/Payroll/payment-mode
    Route::get('master/visibility', [VisibilityControlController::class, 'index'])->name('masters.payroll.visibility');
    Route::get('master/visibility/create', [VisibilityControlController::class, 'create'])->name('masters.payroll.visibility.create');
    Route::get('master/visibility/store', [VisibilityControlController::class, 'store'])->name('masters.payroll.visibility.store');


    // ───────────────────────── HR & WORKFORCE → Employees ─────────────────────────
    // old placeholder URLs now land on the new Employee Directory / Add Employee screens
    Route::redirect('employee', '/employees')->name('employee');
    Route::redirect('employee/create', '/employees/create')->name('employee.create');

    // Employee Directory + Add New Employee
    Route::prefix('employees')->group(function () {

        Route::get('/', [Admin_EmployeesController::class, 'index'])->name('employees.index');
        Route::get('/create', [Admin_EmployeesController::class, 'create'])->name('employees.create');
        Route::post('/store', [Admin_EmployeesController::class, 'store'])->name('employees.store');
        Route::post('/import', [Admin_EmployeesController::class, 'import'])->name('employees.import');
        Route::get('/import/sample', [Admin_EmployeesController::class, 'importSample'])->name('employees.import.sample');
        Route::get('/edit/{employee_id}', [Admin_EmployeesController::class, 'edit'])->name('employees.edit');
        Route::put('/update/{employee_id}', [Admin_EmployeesController::class, 'update'])->name('employees.update');
        Route::delete('/delete/{employee_id}', [Admin_EmployeesController::class, 'destroy'])->name('employees.delete');
        Route::post('/status/{employee_id}', [Admin_EmployeesController::class, 'updateStatus'])->name('employees.status');

        // Document Vault & verification
        Route::prefix('documents')->name('employees.documents.')->group(function () {
            Route::get('/', [DocumentController::class, 'index'])->name('index');
            Route::post('/store', [DocumentController::class, 'store'])->name('store');
            Route::get('/{id}', [DocumentController::class, 'show'])->name('show')->where('id', '[0-9]+');
            Route::post('/{id}/verify', [DocumentController::class, 'verifyStatus'])->name('verify')->where('id', '[0-9]+');
            Route::delete('/{id}', [DocumentController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
            Route::get('/{id}/download', [DocumentController::class, 'download'])->name('download')->where('id', '[0-9]+');
        });

        Route::get('/{employee_id}', [Admin_EmployeesController::class, 'show'])->name('employees.show')->where('employee_id', '[0-9]+');
        Route::post('/{employee_id}/map-subordinates', [Admin_EmployeesController::class, 'mapSubordinates'])->name('employees.mapSubordinates')->where('employee_id', '[0-9]+');
        Route::post('/{employee_id}/unmap-subordinate', [Admin_EmployeesController::class, 'unmapSubordinate'])->name('employees.unmapSubordinate')->where('employee_id', '[0-9]+');

        Route::get('/get-states/{country_id}', [Admin_EmployeesController::class, 'getStates'])->name('employees.getStates');
        Route::get('/get-cities/{state_id}', [Admin_EmployeesController::class, 'getCities'])->name('employees.getCities');
    });

    // Daily Reports (admin view of what employees submitted)
    Route::prefix('employee-report')->group(function () {
        Route::get('/', [EmployeeReportController::class, 'index'])->name('employee-report.index');
        Route::get('/list', [EmployeeReportController::class, 'list'])->name('employee-report.list');
        Route::get('/export', [EmployeeReportController::class, 'export'])->name('employee-report.export');
    });

    // Resignations & Exits
    Route::prefix('admin/resignation')->name('admin.resignation.')->group(function () {
        Route::get('/', [AdminResignationController::class, 'index'])->name('index');
        Route::get('/create', [AdminResignationController::class, 'create'])->name('create');
        Route::post('/store', [AdminResignationController::class, 'store'])->name('store');
        Route::get('/export', [AdminResignationController::class, 'export'])->name('export');
        Route::get('/{id}', [AdminResignationController::class, 'show'])->name('show');
        Route::post('/{id}/respond', [AdminResignationController::class, 'respond'])->name('respond');
        Route::post('/{id}/complete-exit', [AdminResignationController::class, 'completeExit'])->name('complete-exit');
    });

    // Attendance Register Report
    Route::prefix('attendance/attendance-register')->name('attendance.register.')->group(function () {
        Route::get('/', [AttendanceRegisterReportController::class, 'index'])->name('index');
        Route::get('/export/excel', [AttendanceRegisterReportController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [AttendanceRegisterReportController::class, 'exportPdf'])->name('export.pdf');
    });

    // Attendance Reports (Org Check-in / Check-out Reports)
    Route::get('attendance/attendance-dashboard/org-reports', [AttendanceOrgReportController::class, 'index'])->name('attendance.attendance-dashboard.org-reports');
    Route::get('attendance/attendance-dashboard/org-reports/list', [AttendanceOrgReportController::class, 'list'])->name('attendance.attendance-dashboard.org-reports.list');
    Route::get('attendance/attendance-dashboard/org-reports/employee/{employeeId}', [AttendanceOrgReportController::class, 'employeeDetails'])->name('attendance.attendance-dashboard.org-reports.employee');
    Route::get('attendance/attendance-dashboard/org-reports/employee-monthly/{employeeId}', [AttendanceOrgReportController::class, 'employeeMonthlyReport'])->name('attendance.attendance-dashboard.org-reports.employee-monthly');

    // Consistent Attendees
    Route::get('attendance/daily-attendance/consistent-attendees', [ConsistentAttendeesController::class, 'index'])->name('attendance.daily-attendance.consistent-attendees');

    // ───────── User Management (Super Admin only) ─────────
    Route::middleware('superadmin.access')->group(function () {

        // Permissions
        Route::get('masters/user-management/permissions', [PermissionController::class, 'index'])->name('permission');
        Route::get('masters/user-management/permissions/create', [PermissionController::class, 'create'])->name('permission.create');
        Route::post('masters/user-management/permissions/store', [PermissionController::class, 'store'])->name('permission.store');
        Route::get('masters/user-management/permissions/list', [PermissionController::class, 'list'])->name('permission.list');
        Route::get('masters/user-management/permissions/edit/{id}', [PermissionController::class, 'edit'])->name('permissions.edit');
        Route::put('masters/user-management/permissions/update/{id}', [PermissionController::class, 'update'])->name('permissions.update');
        Route::delete('masters/user-management/permissions/destroy/{id}', [PermissionController::class, 'destroy'])->name('permissions.destroy');

        // Roles
        Route::get('masters/user-management/roles', [RoleController::class, 'index'])->name('role');
        Route::get('masters/user-management/roles/create', [RoleController::class, 'create'])->name('role.create');
        Route::post('masters/user-management/roles/store', [RoleController::class, 'store'])->name('role.store');
        Route::get('masters/user-management/roles/list', [RoleController::class, 'list'])->name('role.list');
        Route::get('masters/user-management/roles/edit/{id}', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('masters/user-management/roles/update/{id}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('masters/user-management/roles/destroy/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');

        // Roles & Permissions (single page: Role -> User -> Menu access)
        Route::prefix('access')->name('access.')->group(function () {
            Route::get('/', [AccessController::class, 'index'])->name('index');
            Route::post('scan', [AccessController::class, 'scan'])->name('scan');
            Route::post('save', [AccessController::class, 'saveAccess'])->name('save');
            Route::post('roles', [AccessController::class, 'storeRole'])->name('roles.store');
            Route::put('roles/{role}', [AccessController::class, 'updateRole'])->name('roles.update');
            Route::delete('roles/{role}', [AccessController::class, 'destroyRole'])->name('roles.destroy');
            Route::post('users', [AccessController::class, 'storeUser'])->name('users.store');
            Route::delete('users/{user}', [AccessController::class, 'destroyUser'])->name('users.destroy');
        });

        // Menu access (role -> menus)
        Route::get('map-role-permission', [MapRolePermission::class, 'index'])->name('map-role-permission');
        Route::post('map-role-permission/store', [MapRolePermission::class, 'store'])->name('map-role-permission.store');

        // Users
        Route::prefix('admin/users')->name('admin.users.')->group(function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('index');
            Route::get('create', [AdminUserController::class, 'create'])->name('create');
            Route::post('/', [AdminUserController::class, 'store'])->name('store');
            Route::get('{user}/edit', [AdminUserController::class, 'edit'])->name('edit');
            Route::put('{user}', [AdminUserController::class, 'update'])->name('update');
            Route::delete('{user}', [AdminUserController::class, 'destroy'])->name('destroy');
        });
    });

    Route::get('master/email-template', [SettingController::class, 'index'])->name('settings.email-templates');
    Route::get('master/email-template/create', [SettingController::class, 'create'])->name('settings.email-templates.create');
    Route::post('master/email-template/store', [SettingController::class, 'store'])->name('settings.email-template.store');
    Route::get('master/email-template/list', [SettingController::class, 'list'])->name('settings.email-template.list');
    Route::get('master/email-template/preview/{id}', [SettingController::class, 'preview'])->name('settings.email-template.preview');
    Route::get('master/email-template/edit/{id}', [SettingController::class, 'edit'])->name('settings.email-template.edit');
    Route::put('master/email-template/update/{id}', [SettingController::class, 'update'])->name('settings.email-template.update');
    Route::delete('master/email-template/delete/{id}', [SettingController::class, 'destroy'])->name('settings.email-template.destroy');


    // Master/Organisation/JobSkill


    // Master/Organisation/Category


    Route::get('attendance/leave/request', [AttendanceController::class, 'index'])->name('attendance.leave.request');
    Route::get('attendance/leave/list', [AttendanceController::class, 'list'])->name('leaves.list');
    Route::post('attendance/leave/updateStatus', [AttendanceController::class, 'updateStatus'])->name('leave.updateStatus');
});


Route::get('/', function () {
    return redirect('/superadmin/login');
});
// Email-OTP login (Super Admin, every role panel, Employee)
Route::post('/login-otp/send', [\App\Http\Controllers\Auth\OtpLoginController::class, 'send'])
    ->middleware('throttle:6,1')->name('login.otp.send');
Route::post('/login-otp/verify', [\App\Http\Controllers\Auth\OtpLoginController::class, 'verify'])
    ->middleware('throttle:10,1')->name('login.otp.verify');
Route::get('/superadmin/login', function () {
    return redirect('/vyora-control-portal');
})->name('login.form');
Route::post('/superadmin/login', [AuthController::class, 'login'])->name('login');
Route::get('/vyora-control-portal', [AuthController::class, 'index'])->name('vyora.control.portal.login');
Route::post('/vyora-control-portal', [AuthController::class, 'login'])->name('vyora.control.portal.login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// // Employee
Route::get('/employee/login', [EmployeeAuthController::class, 'index'])->name('employee.login');
Route::post('/employee/login', [EmployeeAuthController::class, 'store'])->name('employee.login');
Route::get('/employee/logout', [EmployeeAuthController::class, 'logout'])->name('employee.logout');

Route::get('recruitment/job/applied-candidate', [AppliedController::class, 'index'])->name('recruitment.jobs.applied-candidate');
Route::get('recruitment/jobs/applied-candidate/list', [AppliedController::class, 'list'])->name('jobs.applied.list');
Route::get('/jobs/details/{id}', [AppliedController::class, 'jobDetails'])->name('jobs.details');

// Employee section routes
Route::prefix('employee')->middleware('auth:employee')->group(function () {

    Route::get('/employee/leave-reasons/{leaveTypeId}', [leavesController::class, 'getLeaveReasons'])->name('employee.leave.reasons');
    // Self Appraisal
    Route::prefix('self-appraisal')->group(function () {

        Route::get('/', [SelfAppraisalController::class, 'index'])->name('employee.self.appraisal');
        Route::get('/competencies', [SelfAppraisalController::class, 'competencies'])->name('employee.competencies');
        Route::get('/kpi-assessment', [SelfAppraisalController::class, 'kpiAssessment'])->name('employee.kpi.assessment');
        Route::get('/form-c', [SelfAppraisalController::class, 'formC'])->name('employee.form-c');
    });

    // Review your Employee

    Route::prefix('review-employee')->group(function () {
        Route::get('/', [ReviewEmployeeController::class, 'index'])->name('employee.review.employee');
    });


    Route::get('/feedback', [FeedbackController::class, 'index'])->name('employee.feedback');
    Route::get('/view-feedback', [ViewFeedbackController::class, 'index'])->name('employee.view-feedback');

    // Dashboard
    Route::get('dashboard', [EmployeeController::class, 'index'])->name('employee.dashboard');
    Route::get('dashboard/data', [EmployeeController::class, 'dashboardData'])->name('employee.dashboard.data');

    // Leaves
    Route::get('leaves', [LeavesController::class, 'index'])->name('employee.leaves');
    Route::get('leaves/apply', [LeavesController::class, 'create'])->name('employee.leaves.apply');
    Route::post('leaves/apply/store', [LeavesController::class, 'store'])->name('employee.leaves.store');
    Route::get('leaves/edit/{id}', [LeavesController::class, 'edit'])->name('employee.leaves.edit');
    Route::put('leaves/update/{id}', [LeavesController::class, 'update'])->name('employee.leaves.update');
    Route::get('leaves/list', [LeavesController::class, 'list'])->name('employee.leaves.list');
    Route::get('leave/balance/{leaveTypeId}', [LeavesController::class, 'getLeaveBalance'])->name('leave.balance');

    // Attendance & Holidayssettings
    Route::get('attendance', [AttendanceCotroller::class, 'index'])->name('employee.attendance');
    Route::post('attendance/punch', [AttendanceCotroller::class, 'punch'])->name('employee.attendance.punch');
    Route::get('attendance/register', [AttendanceCotroller::class, 'register'])->name('employee.attendance.register');

    // Resignation & Exit Management
    Route::prefix('resignation')->name('employee.resignation.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Employee\EmployeeResignationController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Employee\EmployeeResignationController::class, 'create'])->name('create');
        Route::post('/store', [\App\Http\Controllers\Employee\EmployeeResignationController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\Employee\EmployeeResignationController::class, 'show'])->name('show');
        Route::post('/{id}/withdraw', [\App\Http\Controllers\Employee\EmployeeResignationController::class, 'withdraw'])->name('withdraw');
    });
    Route::get('holidays', [AttendanceController::class, 'holidayList'])->name('employee.holidays');

    // Performance
    Route::get('performance', [PerformanceController::class, 'index'])->name('employee.performance');
    Route::get('performance/review', [PerformanceReviewController::class, 'index'])->name('employee.performance.review');

    // Payslip
    // Route::get('payslip', [PayslipController::class, 'index'])->name('employee.payslip');
    Route::get('/checkin-app', [CheckinController::class, 'index']);
    Route::post('/checkin', [CheckinController::class, 'checkin']);
    Route::post('/checkout', [CheckinController::class, 'checkout']);
});


// Resignation: 1-click approve / reject link that goes out in the notification email (token protected)
Route::match(['get', 'post'], 'resignation/action/{token}/{decision?}', [AdminResignationController::class, 'emailAction'])->name('resignation.email-action');

/*
|--------------------------------------------------------------------------
| Role login panels   ->   /{role-name}/login
|--------------------------------------------------------------------------
| Every role created in User Management > Roles automatically gets its own login page,
| exactly like /employee/login   (e.g. "Account Manager"  ->  /account-manager/login).
| Super Admin = "/", Employee = "/employee/login".
|
| KEEP THESE TWO ROUTES AT THE VERY END of the file so that every real route wins first.
*/
Route::get('/{roleSlug}/login', [RoleAuthController::class, 'index'])
    ->where('roleSlug', '[a-z0-9\-]+')->name('role.login');
Route::post('/{roleSlug}/login', [RoleAuthController::class, 'store'])
    ->where('roleSlug', '[a-z0-9\-]+')->name('role.login.submit');
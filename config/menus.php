<?php

/*
|--------------------------------------------------------------------------
| Sidebar menu registry  (single source of truth for the admin sidebar)
|--------------------------------------------------------------------------
|
| Add / change menus HERE, then push them to the database with:
|
|     php artisan menus:sync
|
| Options:  php artisan menus:sync --prune      (also delete menus removed from this file)
|           php artisan menus:sync --fresh      (wipe `menus` + `role_menu` first, then re-create)
|
| Every node supports:
|   key      (required, unique, never change it once synced - it identifies the row in DB)
|   title    (required)
|   icon     (tabler icon class, top-level menus only, e.g. 'ti ti-building')
|   route    (route NAME, or '#' for a parent / placeholder link)
|   section  (optional, top-level only - prints a grey heading such as "HRM" above the menu)
|   permission (optional - Spatie permission name, created automatically and linked to the menu)
|   children (optional - nested nodes, up to 3 levels)
|
| Super Admin always sees every menu. Other roles only see the menus that are
| ticked for them on:  User Management > Menu Access  (role_menu table).
|--------------------------------------------------------------------------
*/

return [

    // ───────────────────────── MASTERS ─────────────────────────
    [
        'key' => 'masters', 'title' => 'Masters', 'icon' => 'ti ti-building', 'route' => '#',
        'children' => [
            ['key' => 'masters.company',      'title' => 'Company',        'route' => 'masters.organisation.company'],
            ['key' => 'masters.branch',       'title' => 'Branch',         'route' => 'masters.organisation.branch'],
            ['key' => 'masters.category',     'title' => 'Category',       'route' => 'settings.category'],
            ['key' => 'masters.department',   'title' => 'Department',     'route' => 'settings.department'],
            ['key' => 'masters.designation',  'title' => 'Designation',    'route' => 'masters.organisation.designation'],
            ['key' => 'masters.shift',        'title' => 'Shift',          'route' => 'masters.organisation.shift'],
            ['key' => 'masters.policy',       'title' => 'Policy',         'route' => 'masters.organisation.policy'],
            ['key' => 'masters.agency',       'title' => 'Agency',         'route' => 'masters.organisation.agency'],
            ['key' => 'masters.agency-map',   'title' => 'Agency Mapping', 'route' => 'masters.organisation.agency.mapping'],
            ['key' => 'masters.skill',        'title' => 'Job Skill',      'route' => 'settings.skills'],
            // key stays 'settings.email-templates' on purpose (keeps existing role access); only the parent changed
            ['key' => 'settings.email-templates', 'title' => 'Email Templates', 'route' => 'settings.email-templates'],
        ],
    ],

    // ───────────────────── HR & WORKFORCE → EMPLOYEES  (sits right after Roles & Permissions) ─────────────────────
    [
        'key' => 'employees', 'title' => 'Employees', 'icon' => 'ti ti-users', 'route' => '#', 'section' => 'HR & WORKFORCE',
        'children' => [
            // keys employees.list / employees.new are kept from the old placeholder so existing role access survives
            ['key' => 'employees.list',          'title' => 'Employee Directory',    'route' => 'employees.index'],
            ['key' => 'employees.new',           'title' => 'Add New Employee',      'route' => 'employees.create'],
            ['key' => 'employees.documents',     'title' => 'Document Vault',        'route' => 'employees.documents.index'],
            ['key' => 'employees.resignations',  'title' => 'Resignations & Exits',  'route' => 'admin.resignation.index'],
            ['key' => 'employees.daily-reports', 'title' => 'Daily Reports',         'route' => 'employee-report.index'],
        ],
    ],

    // ───────────────────── HR & WORKFORCE → ATTENDANCE  (sits right after Employees) ─────────────────────
    [
        'key' => 'attendance', 'title' => 'Attendance', 'icon' => 'ti ti-calendar-time', 'route' => '#', 'section' => 'HR & WORKFORCE',
        'children' => [
            [
                'key' => 'attendance.register', 'title' => 'Attendance Register', 'route' => 'attendance.register.index',
            ],
            [
                'key' => 'attendance.reports', 'title' => 'Attendance Reports', 'route' => 'attendance.attendance-dashboard.org-reports',
            ],
            [
                'key' => 'attendance.consistent', 'title' => 'Consistent Attendees', 'route' => 'attendance.daily-attendance.consistent-attendees',
            ],
        ],
    ],

    // ───────────────────────── SETTINGS ─────────────────────────
    [
        'key' => 'settings', 'title' => 'Settings', 'icon' => 'ti ti-settings', 'route' => '#', 'section' => 'SETTINGS',
        'children' => [
            ['key' => 'settings.holidays',        'title' => 'Holidays',         'route' => 'settings.holiday'],
            ['key' => 'settings.leave-types',     'title' => 'Leave Types',      'route' => 'settings.leave-type'],
            ['key' => 'settings.leave-allowance', 'title' => 'Leave Allowance',  'route' => 'settings.leave-allow'],
        ],
    ],

    // ───────────────────────── HRM ─────────────────────────
    [
        'key' => 'recruitment', 'title' => 'Recruitment', 'icon' => 'ti ti-user-search', 'route' => '#', 'section' => 'HRM',
        'children' => [
            ['key' => 'recruitment.create-job', 'title' => 'Create Job',          'route' => 'recruitment.jobs.create'],
            ['key' => 'recruitment.job-list',   'title' => 'Job List',            'route' => 'recruitment.jobs'],
            ['key' => 'recruitment.jaf',        'title' => 'Customize JAF',       'route' => 'create-job-questionaire'],
            ['key' => 'recruitment.applied',    'title' => 'Applied Candidates',  'route' => 'recruitment.jobs.applied-candidate'],
        ],
    ],
    [
        'key' => 'leave', 'title' => 'Leave Management', 'icon' => 'ti ti-calendar-event', 'route' => '#', 'section' => 'HRM',
        'children' => [
            ['key' => 'leave.requests',   'title' => 'Leave Requests',   'route' => 'attendance.leave.request'],
            ['key' => 'leave.missed',     'title' => 'Missed Punch',     'route' => '#'],
            ['key' => 'leave.compoff',    'title' => 'Comp-Off',         'route' => '#'],
            ['key' => 'leave.overtime',   'title' => 'Overtime',         'route' => '#'],
            ['key' => 'leave.wfh',        'title' => 'WFH',              'route' => '#'],
            ['key' => 'leave.optional',   'title' => 'Optional Holiday', 'route' => '#'],
        ],
    ],

    // ───────────────────────── PAYROLL ─────────────────────────
    [
        'key' => 'payroll.ops', 'title' => 'Payroll Operations', 'icon' => 'ti ti-cash', 'route' => '#', 'section' => 'PAYROLL',
        'children' => [
            ['key' => 'payroll.ops.run',      'title' => 'Run Payroll', 'route' => '#'],
            ['key' => 'payroll.ops.payslips', 'title' => 'Payslips',    'route' => '#'],
        ],
    ],
    [
        'key' => 'payroll.setup', 'title' => 'Payroll Setup', 'icon' => 'ti ti-receipt', 'route' => '#', 'section' => 'PAYROLL',
        'children' => [
            ['key' => 'payroll.setup.component',  'title' => 'Salary Component',   'route' => 'masters.payroll.salary-component'],
            ['key' => 'payroll.setup.structure',  'title' => 'Salary Structure',   'route' => 'masters.payroll.salary-structure'],
            ['key' => 'payroll.setup.rules',      'title' => 'Calculation Rules',  'route' => 'masters.payroll.calculation-rules'],
            ['key' => 'payroll.setup.taxation',   'title' => 'Taxation',           'route' => 'masters.payroll.taxation'],
            ['key' => 'payroll.setup.applicable', 'title' => 'Applicability',      'route' => 'masters.payroll.applicability'],
            ['key' => 'payroll.setup.frequency',  'title' => 'Payment Frequency',  'route' => 'masters.payroll.payment-frequency'],
            ['key' => 'payroll.setup.mode',       'title' => 'Payment Mode',       'route' => 'masters.payroll.payment-mode'],
            ['key' => 'payroll.setup.visibility', 'title' => 'Visibility',         'route' => 'masters.payroll.visibility'],
        ],
    ],

    // ───────────────────────── CLIENTS & PROJECTS ─────────────────────────
    [
        'key' => 'clients', 'title' => 'Clients & Projects', 'icon' => 'ti ti-briefcase', 'route' => '#', 'section' => 'CLIENTS & PROJECTS',
        'children' => [
            ['key' => 'clients.clients',  'title' => 'Clients',  'route' => '#'],
            ['key' => 'clients.projects', 'title' => 'Projects', 'route' => '#'],
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Example - add a new menu, then run:  php artisan menus:sync
    |----------------------------------------------------------------------
    | [
    |     'key' => 'training', 'title' => 'Training', 'icon' => 'ti ti-school', 'route' => '#', 'section' => 'HRM',
    |     'children' => [
    |         ['key' => 'training.list', 'title' => 'Training List', 'route' => 'training.index'],
    |     ],
    | ],
    */
];

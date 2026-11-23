@extends('layout.master')
@section('title', 'Roles & Permissions')

@section('main-section')
    <x-alert-modal :type="session('success') ? 'success' : (session('error') ? 'error' : '')" :message="session('success') ?? session('error')" />

    <style>
        .rp { --p:#2d2765; --p-soft:#ebeaf0; --line:#e6e7ee; --muted:#6b7080; }
        .rp h1.rp-title { font-size:24px; font-weight:700; color:#1f2430; margin:0; }
        .rp .btn-rp { background:var(--p); border-color:var(--p); color:#fff; font-weight:600; }
        .rp .btn-rp:hover { background:#221d52; border-color:#221d52; color:#fff; }
        .rp .btn-rp:disabled { opacity:.6; }
        .rp .btn-rp-line { border:1px solid var(--p); color:var(--p); background:#fff; font-size:12px; font-weight:500; padding:4px 10px; }
        .rp .btn-rp-line:hover { background:var(--p-soft); color:var(--p); }

        /* stat cards */
        .rp .stat { background:#fff; border:1px solid var(--line); border-radius:6px; padding:20px 20px; display:flex; justify-content:space-between; align-items:center; height:100%; box-shadow:0 1px 3px rgba(0,0,0,.04); }
        .rp .stat .lbl { color:var(--muted); font-size:14px; }
        .rp .stat .val { font-size:20px; font-weight:700; color:#1f2430; margin-top:2px; }
        .rp .stat .ico { width:44px; height:44px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:20px; }

        /* tabs */
        .rp .box { background:#fff; border:1px solid var(--line); border-radius:6px; padding:16px 20px 20px; }
        .rp .tabs { display:flex; align-items:center; gap:6px; border-bottom:1px solid var(--line); margin:-16px -20px 18px; padding:16px 20px 0; }
        .rp .tab-btn { display:flex; align-items:center; gap:8px; padding:10px 16px; border:1px solid transparent; border-bottom:0; background:none; font-weight:600; font-size:14px; color:#4b5063; border-radius:6px 6px 0 0; margin-bottom:-1px; }
        .rp .tab-btn .n { width:24px; height:24px; border-radius:50%; background:#e8e9f0; color:#4b5063; font-size:12px; display:inline-flex; align-items:center; justify-content:center; }
        .rp .tab-btn.active { background:#fff; border-color:var(--line); color:#1f2430; }
        .rp .tab-btn.active .n { background:var(--p); color:#fff; }
        .rp .tabs .menus-link { margin-left:auto; font-weight:600; font-size:14px; color:#4b5063; background:none; border:0; }
        .rp .pane { display:none; } .rp .pane.active { display:block; }
        .rp .pane h5 { font-size:17px; font-weight:700; margin:0; color:#1f2430; }
        .rp .pane .sub { font-size:12px; color:var(--muted); }

        /* roles table */
        .rp table.rt { width:100%; margin-top:14px; border-collapse:collapse; }
        .rp table.rt thead th { background:#e5e7eb; padding:10px 20px; font-size:14px; font-weight:700; color:#1f2430; }
        .rp table.rt td { padding:12px 20px; border-bottom:1px solid #edeef3; font-size:14px; vertical-align:middle; }
        .rp .rname { font-weight:700; color:#1f2430; } .rp .rcode { font-size:12px; color:var(--muted); }
        .rp .sysb { font-size:10px; background:#e8f1f8; color:#3b6a8a; border-radius:4px; padding:2px 7px; margin-left:6px; font-weight:600; }
        .rp .pill { font-size:11px; font-weight:700; border-radius:4px; padding:2px 8px; display:inline-block; }
        .rp .pill.u { background:#dbeafe; color:#2563eb; } .rp .pill.m { background:#fde8e6; color:#b4372a; } .rp .pill.a { background:#d1fae5; color:#059669; }
        .rp .act a, .rp .act button { background:none; border:0; color:#1f2430; padding:0 4px; font-size:14px; }
        .rp .act .map { font-weight:700; margin-right:6px; text-decoration:none; }
        .rp .act .del { color:#dc2626; } .rp .act .dis { color:#b8bcc8; cursor:not-allowed; }

        .rp .form-control, .rp .form-select { border-radius:6px; font-size:14px; padding:9px 12px; }
        .rp .form-control:focus, .rp .form-select:focus { border-color:var(--p); box-shadow:0 0 0 3px rgba(45,39,101,.12); }
        .rp label.fl { font-size:14px; font-weight:600; color:#1f2430; margin-bottom:6px; }

        /* matrix */
        .rp .mwrap { border:1px solid var(--line); border-radius:4px; overflow:hidden; }
        .rp .mscroll { max-height:560px; overflow:auto; }
        .rp table.mx { width:100%; border-collapse:separate; border-spacing:0; }
        .rp table.mx thead th { position:sticky; top:0; z-index:2; background:#e5e7eb; font-size:13px; font-weight:700; padding:12px 16px; text-align:center; }
        .rp table.mx thead th.l { text-align:left; }
        .rp table.mx td { padding:11px 16px; border-bottom:1px solid #f0f1f5; font-size:14px; text-align:center; vertical-align:middle; }
        .rp table.mx td.l { text-align:left; }
        .rp table.mx.compact td { padding:5px 16px; font-size:13px; }
        .rp table.mx tr.grp td { background:#f7f7fa; font-weight:700; }
        .rp table.mx tr.gone { display:none; }
        .rp .cb { width:16px; height:16px; accent-color:#6b6b8f; cursor:pointer; }
        .rp .na { color:#9aa0b3; }
        .rp .path { font-family:monospace; font-size:12px; color:#4b5063; }
        .rp .caret { cursor:pointer; color:var(--muted); margin-right:6px; transition:.15s; display:inline-block; } .rp .caret.closed { transform:rotate(-90deg); }
        .rp.hide-path .pathcol { display:none; }
        .rp .info { background:#e6f0fd; border:1px solid #cfe0f7; color:#2563eb; border-radius:4px; padding:10px 14px; font-size:13px; margin:12px 0; }
        .rp .tool { display:flex; flex-wrap:wrap; gap:8px; align-items:center; margin:10px 0; }
    </style>

    <div class="page-wrapper rp" id="rpRoot">
        <div class="content container-fluid">

            <div class="d-flex align-items-start justify-content-between mb-3">
                <div>
                    <h1 class="rp-title">Roles &amp; Permissions</h1>
                    <nav><ol class="breadcrumb mb-0 mt-1" style="font-size:12px;">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="ti ti-home"></i></a></li>
                        <li class="breadcrumb-item">User Management</li>
                        <li class="breadcrumb-item active">Roles &amp; Permissions</li>
                    </ol></nav>
                </div>
                <button class="btn btn-rp" data-bs-toggle="modal" data-bs-target="#addRoleModal"><i class="ti ti-circle-plus me-1"></i> Add Role</button>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-3"><div class="stat"><div><div class="lbl">Total Roles</div><div class="val">{{ $stats['totalRoles'] }}</div></div><div class="ico" style="background:#fde8e6;color:#2d2765"><i class="ti ti-shield-check"></i></div></div></div>
                <div class="col-md-3"><div class="stat"><div><div class="lbl">Active Roles</div><div class="val">{{ $stats['activeRoles'] }}</div></div><div class="ico" style="background:#c9f3dc;color:#16a34a"><i class="ti ti-circle-check"></i></div></div></div>
                <div class="col-md-3"><div class="stat"><div><div class="lbl">Users With a Role</div><div class="val">{{ $stats['usersWithRole'] }}</div></div><div class="ico" style="background:#cfe3fb;color:#2563eb"><i class="ti ti-users"></i></div></div></div>
                <div class="col-md-3"><div class="stat"><div><div class="lbl">Active Menus</div><div class="val">{{ $stats['activeMenus'] }}</div></div><div class="ico" style="background:#fdf0c0;color:#d99a00"><i class="ti ti-menu-2"></i></div></div></div>
            </div>

            <div class="box">
                <div class="tabs">
                    <button type="button" class="tab-btn {{ $tab === 'role' ? 'active' : '' }}" data-tab="role"><span class="n">1</span> Role</button>
                    <button type="button" class="tab-btn {{ $tab === 'user' ? 'active' : '' }}" data-tab="user"><span class="n">2</span> Create User</button>
                    <button type="button" class="tab-btn {{ $tab === 'perm' ? 'active' : '' }}" data-tab="perm"><span class="n">3</span> Assign Permissions</button>
                    <form method="POST" action="{{ route('access.scan') }}" class="ms-auto m-0">@csrf
                        <button class="menus-link" title="Scan for newly added menus"><i class="ti ti-menu-2 me-1"></i> Menus</button>
                    </form>
                </div>

                {{-- ═════════ TAB 1 : ROLE ═════════ --}}
                <div class="pane {{ $tab === 'role' ? 'active' : '' }}" id="pane-role">
                    <div class="d-flex justify-content-between align-items-end">
                        <div><h5>Roles</h5><div class="sub">Step 1 — create the role first, then add users and set permissions.</div></div>
                        <div class="input-group" style="width:300px;"><span class="input-group-text bg-white"><i class="ti ti-search"></i></span>
                            <input type="text" id="roleSearch" class="form-control" placeholder="Search roles..."></div>
                    </div>
                    <table class="rt" id="roleTable">
                        <thead><tr><th style="text-align:left">Role</th><th style="text-align:left">Description</th><th>Users</th><th>Menus</th><th>Status</th><th style="text-align:right">Actions</th></tr></thead>
                        <tbody>
                        @foreach ($roles as $r)
                            <tr data-name="{{ strtolower($r->name) }}">
                                <td><div class="rname">{{ $r->name }}@if($r->isSystem())<span class="sysb">System</span>@endif</div><div class="rcode">{{ strtoupper(str_replace(' ', '_', $r->name)) }}</div></td>
                                <td style="color:#4b5063">{{ $r->description ?: '—' }}</td>
                                <td style="text-align:center">
                                    @if ($r->isEmployeeRole())
                                        <a href="{{ route('employee') }}" class="pill u" title="Open Employee list" style="text-decoration:none">{{ $userCounts[$r->id] ?? 0 }}</a>
                                    @else
                                        <button type="button" class="pill u btn-users" data-role="{{ $r->id }}" title="View users" style="border:0;cursor:pointer">{{ $userCounts[$r->id] ?? 0 }}</button>
                                    @endif
                                </td>
                                <td style="text-align:center"><span class="pill m">{{ $r->isSuperAdminRole() ? 'All' : ($menuCounts[$r->id] ?? 0) }}</span></td>
                                <td style="text-align:center"><span class="pill a">Active</span></td>
                                <td class="act" style="text-align:right;white-space:nowrap">
                                    <a class="map" href="{{ route('access.index', ['tab' => 'perm', 'role' => $r->id]) }}">Map menus</a>
                                    <button type="button" title="Edit" class="btn-edit-role" data-id="{{ $r->id }}" data-name="{{ $r->name }}" data-desc="{{ $r->description }}" data-system="{{ $r->isSystem() ? 1 : 0 }}"><i class="ti ti-pencil"></i></button>
                                    @if ($r->isSystem())
                                        <button type="button" class="dis" disabled><i class="ti ti-trash"></i></button>
                                    @else
                                        <button type="button" class="del btn-del-role" data-id="{{ $r->id }}" data-name="{{ $r->name }}"><i class="ti ti-trash"></i></button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <div class="text-end mt-4"><button type="button" class="btn btn-rp" data-go="user">Next: Create user <i class="ti ti-arrow-right ms-1"></i></button></div>
                </div>

                {{-- ═════════ TAB 2 : CREATE USER ═════════ --}}
                <div class="pane {{ $tab === 'user' ? 'active' : '' }}" id="pane-user">
                    <h5>Create User</h5>
                    <div class="sub mb-3">Step 2 — the user gets the permissions of the role you pick.</div>
                    <form method="POST" action="{{ route('access.users.store') }}" autocomplete="off">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6"><label class="fl">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" class="form-control" required></div>
                            <div class="col-md-6"><label class="fl">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            <div class="col-md-6"><label class="fl">Phone</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" class="form-control"></div>
                            <div class="col-md-6"><label class="fl">Initial Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" name="password" id="pw" class="form-control @error('password') is-invalid @enderror" minlength="8" required autocomplete="new-password">
                                    <button type="button" class="btn btn-outline-secondary" id="pwEye" style="border-color:#ced4da"><i class="ti ti-eye"></i></button>
                                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="sub mt-1">Minimum 8 characters.</div></div>
                            <div class="col-md-6"><label class="fl">Role <span class="text-danger">*</span></label>
                                <select name="role_id" class="form-select" required>
                                    <option value="">Select a role…</option>
                                    @foreach ($roles->reject(fn ($r) => $r->isEmployeeRole()) as $r)
                                        <option value="{{ $r->id }}" {{ (old('role_id') ?? ($selected->id ?? null)) == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                                    @endforeach
                                </select></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('admin.users.index') }}" style="color:var(--p);font-size:14px;"><i class="ti ti-users me-1"></i> Manage existing users</a>
                            <button class="btn btn-rp"><i class="ti ti-user-plus me-1"></i> Create User</button>
                        </div>
                    </form>

                    {{-- users of every role --}}
                    <hr class="my-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="m-0">Users</h5>
                        <select id="userRoleFilter" class="form-select" style="width:230px;font-size:13px;">
                            <option value="">All roles</option>
                            @foreach ($roles->reject(fn ($r) => $r->isEmployeeRole()) as $r)
                                <option value="{{ $r->id }}">{{ $r->name }} ({{ $userCounts[$r->id] ?? 0 }})</option>
                            @endforeach
                        </select>
                    </div>
                    <table class="rt" id="userTable" style="margin-top:6px">
                        <thead><tr><th style="text-align:left">Name</th><th style="text-align:left">Email</th><th style="text-align:left">Phone</th><th style="text-align:left">Role</th><th>Status</th><th style="text-align:right">Action</th></tr></thead>
                        <tbody>
                        @forelse ($allUsers as $u)
                            <tr data-role="{{ $u->role_id }}">
                                <td class="rname">{{ $u->name }}</td>
                                <td>{{ $u->email }}</td>
                                <td>{{ $u->phone ?: '—' }}</td>
                                <td>{{ $u->roleModel->name ?? '—' }}</td>
                                <td style="text-align:center"><span class="pill {{ $u->status ? 'a' : 'm' }}">{{ $u->status ? 'Active' : 'Inactive' }}</span></td>
                                <td class="act" style="text-align:right">
                                    @if (auth()->id() !== $u->id)
                                        <form method="POST" action="{{ route('access.users.destroy', $u->id) }}" class="d-inline" onsubmit="return confirm('Remove {{ addslashes($u->name) }}?')">@csrf @method('DELETE')
                                            <button class="del" title="Remove user"><i class="ti ti-trash"></i></button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No users yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-between mt-3">
                        <button type="button" class="btn btn-light" data-go="role"><i class="ti ti-arrow-left me-1"></i> Back</button>
                        <button type="button" class="btn btn-rp" data-go="perm">Next: Assign permissions <i class="ti ti-arrow-right ms-1"></i></button>
                    </div>
                </div>

                {{-- ═════════ TAB 3 : ASSIGN PERMISSIONS ═════════ --}}
                <div class="pane {{ $tab === 'perm' ? 'active' : '' }}" id="pane-perm">
                    <label class="fl">Role <span class="text-danger">*</span></label>
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <select class="form-select" style="max-width:430px" onchange="location='{{ (\Illuminate\Support\Facades\Route::has('access.index') ? route('access.index') : '#') }}?tab=perm&role='+this.value">
                            @foreach ($roles as $r)
                                <option value="{{ $r->id }}" {{ $selected && $selected->id === $r->id ? 'selected' : '' }}>{{ $r->name }} ({{ strtoupper(str_replace(' ', '_', $r->name)) }})</option>
                            @endforeach
                        </select>
                        @if ($selected && !$selected->isSuperAdminRole())
                            <button type="button" class="btn btn-rp" id="btnSave"><i class="ti ti-device-floppy me-1"></i> Save Changes</button>
                        @else
                            <button type="button" class="btn btn-rp" disabled style="opacity:.6"><i class="ti ti-device-floppy me-1"></i> Save Changes</button>
                        @endif
                    </div>

                    @if ($selected && $selected->isSuperAdminRole())
                        <div class="info"><i class="ti ti-shield me-1"></i> Super Admin always has full access to every menu. This role cannot be restricted.</div>
                    @endif

                    <div class="tool">
                        <button type="button" class="btn btn-rp-line" id="btnPath"><i class="ti ti-layout-sidebar me-1"></i><span>Hide path</span></button>
                    </div>
                    <select class="form-select mb-2" id="menuFilter" style="font-size:13px;padding:6px 12px;">
                        <option value="">All menus</option>
                        @foreach (collect($rows)->where('depth', 0) as $top)
                            <option value="{{ $top['id'] }}">{{ $top['title'] }}</option>
                        @endforeach
                    </select>
                    <div class="tool justify-content-between">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-rp-line" id="btnCompact"><i class="ti ti-list me-1"></i>Compact</button>
                            <button type="button" class="btn btn-rp-line" id="btnToggleAll"><i class="ti ti-arrows-vertical me-1"></i>Collapse / expand</button>
                            <button type="button" class="btn btn-rp-line" id="btnExport"><i class="ti ti-download me-1"></i>Export</button>
                        </div>
                        <div class="input-group" style="width:280px;"><span class="input-group-text bg-white"><i class="ti ti-search"></i></span>
                            <input type="text" id="menuSearch" class="form-control" placeholder="Search menus..."></div>
                    </div>

                    @php $locked = !$selected || $selected->isSuperAdminRole(); $all = $selected && $selected->isSuperAdminRole(); @endphp
                    <div class="mwrap"><div class="mscroll">
                        <table class="mx" id="matrix">
                            <thead><tr>
                                <th class="l" style="width:34%">Menu</th><th class="l pathcol">Path</th>
                                @foreach (['view' => 'View', 'add' => 'Add', 'edit' => 'Edit', 'delete' => 'Delete'] as $k => $lb)
                                    <th style="width:90px">{{ $lb }}<div><input type="checkbox" class="cb col-all" data-col="{{ $k }}" {{ $locked ? 'disabled' : '' }} {{ $all ? 'checked' : '' }}></div></th>
                                @endforeach
                            </tr></thead>
                            <tbody>
                            @foreach ($rows as $row)
                                <tr class="{{ $row['group'] ? 'grp' : '' }}" data-id="{{ $row['id'] }}" data-parent="{{ $row['parent'] }}" data-title="{{ strtolower($row['title']) }}" data-name="{{ $row['title'] }}">
                                    <td class="l" style="padding-left:{{ 16 + $row['depth'] * 18 }}px">
                                        @if ($row['group'])<i class="ti ti-chevron-down caret"></i>@endif{{ $row['title'] }}
                                    </td>
                                    <td class="l pathcol"><span class="path">{{ $row['group'] || !$row['path'] ? '—' : $row['path'] }}</span></td>
                                    @foreach (['view', 'add', 'edit', 'delete'] as $k)
                                        <td><input type="checkbox" class="cb" data-k="{{ $k }}" {{ ($row[$k] || $all) ? 'checked' : '' }} {{ $locked ? 'disabled' : '' }}></td>
                                    @endforeach
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div></div>
                    <div class="d-flex justify-content-between mt-3">
                        <button type="button" class="btn btn-light" data-go="user"><i class="ti ti-arrow-left me-1"></i> Back</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add / Edit role modals --}}
    <div class="modal fade rp" id="addRoleModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST" action="{{ route('access.roles.store') }}">@csrf
        <div class="modal-header"><h5 class="modal-title">Add Role</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="fl">Role name <span class="text-danger">*</span></label>
                <input type="text" name="role_name" value="{{ old('role_name') }}" class="form-control @error('role_name') is-invalid @enderror" maxlength="60" required>
                @error('role_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div><label class="fl">Description</label><textarea name="description" class="form-control" rows="3" maxlength="500">{{ old('description') }}</textarea></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-rp">Create role</button></div>
    </form></div></div>

    <div class="modal fade rp" id="editRoleModal" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST" id="editRoleForm">@csrf @method('PUT')
        <div class="modal-header"><h5 class="modal-title">Edit Role</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="fl">Role name</label><input type="text" name="role_name" id="erName" class="form-control" maxlength="60" required></div>
            <div><label class="fl">Description</label><textarea name="description" id="erDesc" class="form-control" rows="3" maxlength="500"></textarea></div>
            <div class="sub mt-2" id="erNote" style="display:none;font-size:12px;color:#6b7080">System role: only the description can be changed.</div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-rp">Save</button></div>
    </form></div></div>

    <form id="delRoleForm" method="POST" style="display:none">@csrf @method('DELETE')</form>
@endsection

@push('after_scripts')
<script>
(function () {
    var root = document.getElementById('rpRoot');

    // ---- tabs ----
    function go(t) {
        root.querySelectorAll('.tab-btn').forEach(function (b) { b.classList.toggle('active', b.dataset.tab === t); });
        root.querySelectorAll('.pane').forEach(function (p) { p.classList.toggle('active', p.id === 'pane-' + t); });
    }
    root.querySelectorAll('[data-tab]').forEach(function (b) { b.onclick = function () { go(b.dataset.tab); }; });
    root.querySelectorAll('[data-go]').forEach(function (b) { b.onclick = function () { go(b.dataset.go); }; });

    // ---- role table ----
    document.getElementById('roleSearch').addEventListener('input', function () {
        var q = this.value.toLowerCase();
        document.querySelectorAll('#roleTable tbody tr').forEach(function (r) { r.style.display = r.dataset.name.indexOf(q) > -1 ? '' : 'none'; });
    });
    document.querySelectorAll('.btn-edit-role').forEach(function (b) {
        b.onclick = function () {
            document.getElementById('editRoleForm').action = '{{ route('access.roles.update', ['role' => '__ROLE_ID__']) }}'.replace('__ROLE_ID__', encodeURIComponent(b.dataset.id));
            var n = document.getElementById('erName'); n.value = b.dataset.name; n.readOnly = b.dataset.system === '1';
            document.getElementById('erDesc').value = b.dataset.desc || '';
            document.getElementById('erNote').style.display = b.dataset.system === '1' ? 'block' : 'none';
            new bootstrap.Modal(document.getElementById('editRoleModal')).show();
        };
    });
    document.querySelectorAll('.btn-del-role').forEach(function (b) {
        b.onclick = function () {
            Swal.fire({title: 'Delete role?', text: b.dataset.name, icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', confirmButtonText: 'Yes, delete'})
                .then(function (r) { if (r.isConfirmed) { var f = document.getElementById('delRoleForm'); f.action = '{{ route('access.roles.destroy', ['role' => '__ROLE_ID__']) }}'.replace('__ROLE_ID__', encodeURIComponent(b.dataset.id)); f.submit(); } });
        };
    });
    @if ($errors->has('role_name') && old('role_name')) new bootstrap.Modal(document.getElementById('addRoleModal')).show(); @endif

    // ---- users list filter ----
    var uf = document.getElementById('userRoleFilter');
    function filterUsers() { document.querySelectorAll('#userTable tbody tr[data-role]').forEach(function (r) { r.style.display = !uf.value || r.dataset.role === uf.value ? '' : 'none'; }); }
    uf.addEventListener('change', filterUsers);
    document.querySelectorAll('.btn-users').forEach(function (b) { b.onclick = function () { uf.value = b.dataset.role; filterUsers(); go('user'); document.getElementById('userTable').scrollIntoView({behavior: 'smooth'}); }; });

    // ---- user form ----
    document.getElementById('pwEye').onclick = function () { var i = document.getElementById('pw'); i.type = i.type === 'password' ? 'text' : 'password'; };

    // ---- permissions matrix ----
    var rows = Array.from(document.querySelectorAll('#matrix tbody tr')), KEYS = ['view', 'add', 'edit', 'delete'];
    var editable = {{ ($selected && !$selected->isSuperAdminRole()) ? 'true' : 'false' }};
    function cb(tr, k) { return tr.querySelector('input[data-k="' + k + '"]'); }
    function kids(tr) { return rows.filter(function (r) { return r.dataset.parent === tr.dataset.id; }); }
    function desc(tr) { var o = []; kids(tr).forEach(function (c) { o.push(c); o = o.concat(desc(c)); }); return o; }
    function parentOf(tr) { return rows.find(function (r) { return r.dataset.id === tr.dataset.parent; }); }
    function isGrp(r) { return r.classList.contains('grp'); }
    function norm(tr) { if (KEYS.slice(1).some(function (k) { return cb(tr, k).checked; })) cb(tr, 'view').checked = true; }
    function refresh() {
        rows.filter(isGrp).reverse().forEach(function (g) {
            var d = desc(g).filter(function (r) { return !isGrp(r); });
            KEYS.forEach(function (k) {
                var all = d.length && d.every(function (r) { return cb(r, k).checked; }), some = d.some(function (r) { return cb(r, k).checked; });
                var b = cb(g, k); b.checked = !!all; b.indeterminate = !all && some;
            });
        });
        var leaves = rows.filter(function (r) { return !isGrp(r); });
        KEYS.forEach(function (k) {
            var h = document.querySelector('.col-all[data-col="' + k + '"]'), n = leaves.filter(function (r) { return cb(r, k).checked; }).length;
            h.checked = leaves.length > 0 && n === leaves.length; h.indeterminate = n > 0 && n < leaves.length;
        });
    }
    if (rows.length) {
        document.querySelector('#matrix tbody').addEventListener('change', function (e) {
            var t = e.target; if (!t.dataset.k) return; var tr = t.closest('tr'), k = t.dataset.k;
            var targets = isGrp(tr) ? desc(tr).concat([tr]) : [tr];
            targets.forEach(function (r) {
                cb(r, k).checked = t.checked;
                if (k === 'view' && !t.checked) KEYS.forEach(function (x) { cb(r, x).checked = false; }); else norm(r);
            });
            refresh();
        });
        document.querySelectorAll('.col-all').forEach(function (h) {
            h.onchange = function () {
                var k = h.dataset.col;
                rows.forEach(function (r) { cb(r, k).checked = h.checked; if (k === 'view' && !h.checked) KEYS.forEach(function (x) { cb(r, x).checked = false; }); else norm(r); });
                refresh();
            };
        });
        function setOpen(tr, open) {
            tr.querySelector('.caret').classList.toggle('closed', !open);
            desc(tr).forEach(function (r) { r.classList.toggle('gone', !open); });
            if (open) desc(tr).forEach(function (r) { var p = parentOf(r); if (p && p.querySelector('.caret.closed')) r.classList.add('gone'); });
        }
        document.querySelectorAll('#matrix .caret').forEach(function (c) { c.onclick = function () { setOpen(c.closest('tr'), c.classList.contains('closed')); }; });
        var open = true;
        document.getElementById('btnToggleAll').onclick = function () { open = !open; rows.filter(isGrp).forEach(function (g) { setOpen(g, open); }); };
        document.getElementById('btnCompact').onclick = function () { document.getElementById('matrix').classList.toggle('compact'); };
        document.getElementById('btnPath').onclick = function () { var h = root.classList.toggle('hide-path'); this.querySelector('span').textContent = h ? 'Show path' : 'Hide path'; };
        function applyFilter() {
            var q = document.getElementById('menuSearch').value.trim().toLowerCase(), f = document.getElementById('menuFilter').value;
            rows.forEach(function (r) {
                var top = r; while (parentOf(top)) top = parentOf(top);
                var okF = !f || top.dataset.id === f;
                var hit = !q || r.dataset.title.indexOf(q) > -1 || desc(r).some(function (d) { return d.dataset.title.indexOf(q) > -1; });
                var up = parentOf(r), ph = false; while (up) { if (q && up.dataset.title.indexOf(q) > -1) ph = true; up = parentOf(up); }
                r.classList.toggle('gone', !(okF && (hit || ph)));
            });
        }
        document.getElementById('menuSearch').addEventListener('input', applyFilter);
        document.getElementById('menuFilter').addEventListener('change', applyFilter);
        document.getElementById('btnExport').onclick = function () {
            var out = [['Menu', 'Path', 'View', 'Add', 'Edit', 'Delete']];
            rows.forEach(function (r) {
                var tds = r.querySelectorAll('td');
                out.push([r.dataset.name, tds[1].textContent.trim()].concat(KEYS.map(function (k) { return cb(r, k).checked ? 'Yes' : 'No'; })));
            });
            var csv = out.map(function (l) { return l.map(function (x) { return '"' + String(x).replace(/"/g, '""') + '"'; }).join(','); }).join('\n');
            var a = document.createElement('a'); a.href = URL.createObjectURL(new Blob([csv], {type: 'text/csv'})); a.download = 'role-permissions.csv'; a.click();
        };
        var save = document.getElementById('btnSave');
        if (save && editable) save.onclick = function () {
            var perms = {};
            rows.forEach(function (r) { var p = {}, any = false; KEYS.forEach(function (k) { if (cb(r, k).checked) { p[k] = 1; any = true; } }); if (any) perms[r.dataset.id] = p; });
            save.disabled = true;
            fetch('{{ route('access.save') }}', {method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({role_id: {{ $selected->id ?? 0 }}, perms: perms})})
                .then(function (r) { return r.json(); })
                .then(function (d) { Swal.fire({icon: d.status ? 'success' : 'error', text: d.message, timer: 1800, showConfirmButton: false}); })
                .catch(function () { Swal.fire({icon: 'error', text: 'Could not save. Please try again.'}); })
                .finally(function () { save.disabled = false; });
        };
        refresh();
    }
})();
</script>
@endpush

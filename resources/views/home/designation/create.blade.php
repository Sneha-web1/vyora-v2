@extends('layout.master')
@section('title', $title)
@section('main-section')

    <div class="page-wrapper">
        <div class="content">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                                <div class="my-auto mb-2">
                                    <h2 class="mb-1">{{ $title }}</h2>
                                </div>
                                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                                    <div class="mb-2">
                                        <a href="{{ route('masters.organisation.designation') }}"
                                            class="btn btn-primary d-flex align-items-center">
                                            <i class="ti ti-list-details me-2"></i> Designation List
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <form class="needs-validation" novalidate method="POST" action="{{ route('masters.organisation.designation.store') }}">
                                @csrf
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" for="company_id">Select Organization <span class="text-danger">*</span></label>
                                        <select class="form-control select2" id="company_id" name="company_id" required>
                                            <option value="">Select Organization</option>
                                            @foreach ($companies as $company)
                                                <option value="{{ $company->id }}" {{ old('company_id', $loop->count === 1 ? $company->id : null) == $company->id ? 'selected' : '' }}>{{ $company->company_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" for="branch_id">Branch</label>
                                        <select class="form-control select2" id="branch_id" name="branch_id">
                                            <option value="">Select Branch</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" for="category_id">Category</label>
                                        <select class="form-control select2" id="category_id" name="category_id">
                                            <option value="">Select Category</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" for="department_id">Department</label>
                                        <select class="form-control select2" id="department_id" name="department_id">
                                            <option value="">Select Department</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" for="name">Designation Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" placeholder="Designation Name"
                                            value="{{ old('name') }}" required>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" for="designation_code">Designation Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="designation_code" name="designation_code"
                                            placeholder="Enter Designation Code"
                                            value="{{ old('designation_code') }}" required>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" for="kpi_weightage">KPI Weightage <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" max="100" class="form-control" id="kpi_weightage"
                                            name="kpi_weightage" placeholder="Enter KPI Weightage"
                                            value="{{ old('kpi_weightage') }}" required>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" for="competency_weight">Competency Weight <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" max="100" class="form-control" id="competency_weight"
                                            name="competency_weight" placeholder="Enter Competency Weight"
                                            value="{{ old('competency_weight') }}" required>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label" for="status">Status <span class="text-danger">*</span></label>
                                        <select class="form-control select2" id="status" name="status" required>
                                            <option value="Active" {{ old('status', 'Active') == 'Active' ? 'selected' : '' }}>Active</option>
                                            <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-3">
                                    <a href="{{ route('masters.organisation.designation') }}" class="btn btn-light me-2">
                                        <i class="ti ti-arrow-left me-1"></i> Back
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-device-floppy me-1"></i> Save Designation
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-footer />
    </div>
@endsection

@push('after_scripts')
    <script>
        $(document).ready(function() {
            var branchUrl = "{{ url('master/branch/by-company') }}";
            var deptUrl = "{{ url('master/department/by-company') }}";

            function esc(t) { return $('<div>').text(t).html(); }

            function loadBranches(companyId, selectedId) {
                var $b = $('#branch_id');
                $b.html('<option value="">Select Branch</option>');
                if (!companyId) { $b.trigger('change.select2'); return; }
                $.getJSON(branchUrl + '/' + companyId, function(rows) {
                    $.each(rows, function(i, r) {
                        var sel = (selectedId && String(selectedId) === String(r.id)) ? ' selected' : '';
                        $b.append('<option value="' + r.id + '"' + sel + '>' + esc(r.branch_name) + '</option>');
                    });
                    $b.trigger('change.select2');
                });
            }

            function loadDepartments(companyId, branchId, selectedId) {
                var $d = $('#department_id');
                $d.html('<option value="">Select Department</option>');
                if (!companyId) { $d.trigger('change.select2'); return; }
                $.getJSON(deptUrl + '/' + companyId, { branch_id: branchId || '' }, function(rows) {
                    $.each(rows, function(i, r) {
                        var sel = (selectedId && String(selectedId) === String(r.id)) ? ' selected' : '';
                        $d.append('<option value="' + r.id + '"' + sel + '>' + esc(r.department_name) + '</option>');
                    });
                    $d.trigger('change.select2');
                });
            }

            $('#company_id').on('change', function() {
                loadBranches($(this).val(), null);
                loadDepartments($(this).val(), null, null);
            });
            $('#branch_id').on('change', function() {
                loadDepartments($('#company_id').val(), $(this).val(), null);
            });
            @if ($preCompanyId = old('company_id', $companies->count() === 1 ? $companies->first()->id : null))
                loadBranches("{{ $preCompanyId }}", "{{ old('branch_id') }}");
                loadDepartments("{{ $preCompanyId }}", "{{ old('branch_id') }}", "{{ old('department_id') }}");
            @endif
        });
    </script>
@endpush
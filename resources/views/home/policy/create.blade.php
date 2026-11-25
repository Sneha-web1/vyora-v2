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
                                        <a href="{{ route('masters.organisation.policy') }}"
                                            class="btn btn-primary d-flex align-items-center">
                                            <i class="ti ti-list-details me-2"></i> Policy List
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <form class="needs-validation" novalidate method="POST"
                                action="{{ route('masters.organisation.policy.store') }}" enctype="multipart/form-data">
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
                                        <label class="form-label">Policy Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="policy_title"
                                            placeholder="Enter Policy Title" required>
                                        <div class="invalid-feedback">Please enter policy title.</div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Policy Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="policy_code"
                                            placeholder="Enter Policy Code" required>
                                        <div class="invalid-feedback">Please enter policy code.</div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Organization <span class="text-danger">*</span></label>
                                        <select class="form-control select2" id="company_id" name="company_id" required>
                                            <option value="">Select Organization</option>
                                            @foreach ($companies as $company)
                                                <option value="{{ $company->id }}" {{ old('company_id', $loop->count === 1 ? $company->id : null) == $company->id ? 'selected' : '' }}>{{ $company->company_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('company_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        <div class="invalid-feedback">Please select company.</div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Branch</label>
                                        <select class="form-control select2" id="branch_id" name="branch_id">
                                            <option value="">Select Branch</option>
                                        </select>
                                        @error('branch_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Department</label>
                                        <select class="form-control select2" id="department_id" name="department_id">
                                            <option value="">Select Department</option>
                                        </select>
                                        @error('department_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Effective From <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="effective_from" required>
                                        <div class="invalid-feedback">Please select effective date.</div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Expiry Date</label>
                                        <input type="date" class="form-control" name="expiry_date">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Upload Policy Document (PDF)</label>
                                        <input type="file" class="form-control" name="policy_file" accept=".pdf">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-control select2" name="status" required>
                                            <option value="">Select Status</option>
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                        </select>
                                        <div class="invalid-feedback">Please select status.</div>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea id="policy_description" class="form-control" name="description" rows="4"
                                            placeholder="Enter policy details"></textarea>
                                    </div>
                                </div>

                                <div class="text-end mt-3">
                                    <a href="{{ route('masters.organisation.policy') }}" class="btn btn-light me-2">
                                        <i class="ti ti-arrow-left me-1"></i> Back
                                    </a>
                                    <button type="reset" class="btn btn-secondary me-2">
                                        <i class="ti ti-x me-1"></i> Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-device-floppy me-1"></i> Save Policy
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
    <script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>
    <script>
        ClassicEditor
            .create(document.querySelector('#policy_description'))
            .catch(error => {
                console.error(error);
            });
    </script>
@endpush

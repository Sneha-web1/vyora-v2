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
                                action="{{ route('masters.organisation.policy.update', $policy->id) }}"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
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
                                            placeholder="Enter Policy Title"
                                            value="{{ old('policy_title', $policy->policy_title) }}" required>
                                        <div class="invalid-feedback">Please enter policy title.</div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Policy Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="policy_code"
                                            placeholder="Enter Policy Code"
                                            value="{{ old('policy_code', $policy->policy_code) }}" required>
                                        <div class="invalid-feedback">Please enter policy code.</div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Organization <span class="text-danger">*</span></label>
                                        <select class="form-control select2" id="company_id" name="company_id" required>
                                            <option value="">Select Organization</option>
                                            @foreach ($companies as $company)
                                                <option value="{{ $company->id }}" {{ old('company_id', $policy->company_id) == $company->id ? 'selected' : '' }}>{{ $company->company_name }}</option>
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
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}" {{ old('branch_id', $policy->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->branch_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('branch_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Department</label>
                                        <select class="form-control select2" id="department_id" name="department_id">
                                            <option value="">Select Department</option>
                                            @foreach ($departments as $department)
                                                <option value="{{ $department->id }}" {{ old('department_id', $policy->department_id) == $department->id ? 'selected' : '' }}>{{ $department->department_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('department_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Effective From <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="effective_from"
                                            value="{{ old('effective_from', optional($policy->effective_from)->format('Y-m-d')) }}" required>
                                        <div class="invalid-feedback">Please select effective date.</div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Expiry Date</label>
                                        <input type="date" class="form-control" name="expiry_date"
                                            value="{{ old('expiry_date', optional($policy->expiry_date)->format('Y-m-d')) }}">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Upload Policy Document (PDF)</label>
                                        <input type="file" class="form-control" name="policy_file" accept=".pdf">
                                        @if ($policy->policy_document)
                                            <a href="{{ asset($policy->policy_document) }}" target="_blank"
                                                class="d-block mt-2">
                                                View Current Document
                                            </a>
                                        @endif
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-control select2" name="status" required>
                                            <option value="">Select Status</option>
                                            <option value="Active"
                                                {{ old('status', $policy->status) == 'Active' ? 'selected' : '' }}>Active
                                            </option>
                                            <option value="Inactive"
                                                {{ old('status', $policy->status) == 'Inactive' ? 'selected' : '' }}>
                                                Inactive</option>
                                        </select>
                                        <div class="invalid-feedback">Please select status.</div>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea id="policy_description" class="form-control" name="description" rows="4"
                                            placeholder="Enter policy details">{{ old('description', $policy->description) }}</textarea>
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
                                        <i class="ti ti-device-floppy me-1"></i> Update Policy
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

@extends('layout.master')
@section('title', $title ?? 'Agency')

@section('main-section')
    @php
        $isEdit = !empty($agency);
        $selCountry = old('country_id', $agency->country_id ?? '');
        $selState = old('state_id', $agency->state_id ?? '');
        $selCity = old('city_id', $agency->city_id ?? '');
        $val = fn($k) => old($k, $agency->{$k} ?? '');
    @endphp

    <x-alert-modal :type="session('success') ? 'success' : (session('error') ? 'error' : '')" :message="session('success') ?? session('error')" />

    <div class="page-wrapper">
        <div class="content">

            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">{{ $title }}</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item" aria-current="page">{{ $titleRoute }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="mb-2">
                    <a href="{{ route('masters.organisation.agency') }}" class="btn btn-light d-flex align-items-center">
                        <i class="ti ti-arrow-left me-2"></i>Back to Agencies
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger py-2 fs-12">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST"
                        action="{{ $isEdit ? route('masters.organisation.agency.update', $agency->id) : route('masters.organisation.agency.store') }}">
                        @csrf
                        @if ($isEdit)
                            @method('PUT')
                        @endif

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Agency Name <span class="text-danger">*</span></label>
                                <input type="text" name="agency_name" class="form-control" value="{{ $val('agency_name') }}"
                                    placeholder="e.g. Apex Staffing Solutions" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Agency Code</label>
                                <input type="text" name="agency_code" class="form-control" value="{{ $val('agency_code') }}"
                                    placeholder="e.g. APX-001">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    @foreach (['Active', 'Inactive'] as $st)
                                        <option value="{{ $st }}" @selected(old('status', $agency->status ?? 'Active') === $st)>{{ $st }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Contact Person</label>
                                <input type="text" name="contact_person" class="form-control"
                                    value="{{ $val('contact_person') }}" placeholder="e.g. Rahul Sharma">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control" value="{{ $val('phone') }}"
                                    placeholder="e.g. +91 9876543210">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Landline Number</label>
                                <input type="text" name="landline_no" class="form-control" value="{{ $val('landline_no') }}"
                                    maxlength="20" placeholder="e.g. 0512-2345678">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" value="{{ $val('email') }}"
                                    placeholder="agency@example.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">GST Number</label>
                                <input type="text" name="gst_no" class="form-control" value="{{ $val('gst_no') }}"
                                    maxlength="20" placeholder="e.g. 09ABCDE1234F1Z5">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Country</label>
                                <select name="country_id" id="country" class="form-select">
                                    <option value="">Select Country</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}" @selected((string) $selCountry === (string) $country->id)>{{ $country->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">State</label>
                                <select name="state_id" id="state" class="form-select">
                                    <option value="">Select State</option>
                                    @foreach ($states as $state)
                                        <option value="{{ $state->id }}" @selected((string) $selState === (string) $state->id)>{{ $state->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">City</label>
                                <select name="city_id" id="city" class="form-select">
                                    <option value="">Select City</option>
                                    @foreach ($cities as $city)
                                        <option value="{{ $city->id }}" @selected((string) $selCity === (string) $city->id)>{{ $city->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label">Office Address</label>
                                <textarea name="address" rows="2" class="form-control" placeholder="Street address, building, suite...">{{ $val('address') }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Office Pincode</label>
                                <input type="text" name="office_pincode" class="form-control" value="{{ $val('office_pincode') }}"
                                    maxlength="10" placeholder="e.g. 208001">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes / Terms</label>
                                <textarea name="notes" rows="2" class="form-control" placeholder="Additional vendor agreements or notes...">{{ $val('notes') }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('masters.organisation.agency') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i>{{ $isEdit ? 'Update Agency' : 'Save Agency' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
        <x-footer />
    </div>
@endsection

@push('after_scripts')
    <script>
        function fillOptions($el, items, placeholder) {
            let html = '<option value="">' + placeholder + '</option>';
            items.forEach(function(i) {
                html += '<option value="' + i.id + '">' + $('<div>').text(i.name).html() + '</option>';
            });
            $el.html(html);
        }

        $('#country').on('change', function() {
            const id = $(this).val();
            fillOptions($('#city'), [], 'Select City');
            if (!id) return fillOptions($('#state'), [], 'Select State');
            $.get("{{ url('get-state') }}/" + id, function(res) {
                fillOptions($('#state'), res, 'Select State');
            });
        });

        $('#state').on('change', function() {
            const id = $(this).val();
            if (!id) return fillOptions($('#city'), [], 'Select City');
            $.get("{{ url('get-cities') }}/" + id, function(res) {
                fillOptions($('#city'), res, 'Select City');
            });
        });
    </script>
@endpush

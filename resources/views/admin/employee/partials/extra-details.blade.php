{{--
    Shared by admin/employee/create.blade.php and edit.blade.php
    Sections: 6. Address & Notice Period | 7. DPDP Consent  (Family Details is now section 2: partials/family-details)
    Expects: $employee (null on create)
--}}
@php
    $emp = $employee ?? null;
    $isCreate = !$emp;


    // ---- DPDP ----
    $consentRecorded = $emp && $emp->dpdp_consent_given;
@endphp

<div class="form-section-divider"></div>

<!-- SECTION 6: ADDRESS & NOTICE PERIOD -->
<div class="employee-section-header">
    <i class="ti ti-home text-primary"></i> 6. Address & Notice Period
</div>

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label fs-13 fw-semibold text-dark mb-1">
            Full Address <span class="text-danger">*</span>
        </label>
        <textarea name="address" rows="3" maxlength="1000" class="form-control form-control-custom"
            placeholder="House / Flat no., Street, Area, City, State, Pincode" required>{{ old('address', $emp->address ?? '') }}</textarea>
        <div class="invalid-feedback">Please enter the employee's full address.</div>
    </div>

    <div class="col-md-4">
        <label class="form-label fs-13 fw-semibold text-dark mb-1">
            Notice Period (Days) <span class="text-danger">*</span>
        </label>
        <div class="input-group input-group-custom">
            <span class="input-group-text"><i class="ti ti-clock-hour-4"></i></span>
            <input type="number" name="notice_period_days" class="form-control form-control-custom" min="0" max="180"
                value="{{ old('notice_period_days', $emp->notice_period_days ?? 30) }}" required>
            <span class="input-group-text">Days</span>
        </div>
        <small class="text-muted d-block mt-1" style="font-size: 11px;">
            This is shown (read-only) to the employee when they apply for resignation.
        </small>
        <div class="invalid-feedback">Enter notice period between 0 and 180 days.</div>
    </div>
</div>

<div class="form-section-divider"></div>

<div class="form-section-divider"></div>

<!-- SECTION 7: DPDP COMPLIANCE & CONSENT -->
<div class="employee-section-header">
    <i class="ti ti-shield-lock text-primary"></i> 7. DPDP Compliance & Consent
</div>

@if ($consentRecorded)
    <div class="alert alert-success border-0 mb-0 fs-13">
        <div class="fw-semibold mb-1"><i class="ti ti-circle-check me-1"></i>DPDP consent recorded</div>
        <div>
            Recorded on <strong>{{ optional($emp->dpdp_consent_at)->format('d M Y') ?? '--' }}</strong>
            via <strong>{{ $emp->dpdp_consent_mode ?? '--' }}</strong>
            (version {{ $emp->dpdp_consent_version ?? '--' }}).
            Sharing with third-party processors:
            <strong>{{ $emp->dpdp_sharing_consent ? 'Allowed' : 'Not allowed' }}</strong>.
        </div>
        @if ($emp->dpdp_consent_recorded_by)
            <div class="text-muted fs-12 mt-1">Recorded by {{ $emp->dpdp_consent_recorded_by }}</div>
        @endif
    </div>
@else
    @if (!$isCreate)
        <div class="alert alert-warning border-0 fs-13">
            <i class="ti ti-alert-triangle me-1"></i>No DPDP consent has been recorded for this employee yet.
            You can record it now.
        </div>
    @endif

    <div class="p-3 rounded border mb-3 fs-13 text-muted" style="background-color: #f8f9fa;">
        Under the <strong>Digital Personal Data Protection Act, 2023</strong>, the organisation collects and processes the
        employee's personal data (identity, contact, address, family and employment details) only for employment,
        payroll, statutory compliance and HR administration. The employee may withdraw consent, and may request access,
        correction or erasure of their data, by contacting HR / the Data Protection Officer.
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="dpdp_privacy_notice_ack" id="dpdp_privacy_notice_ack"
                    value="1" @checked(old('dpdp_privacy_notice_ack')) @if ($isCreate) required @endif>
                <label class="form-check-label fs-13 text-dark" for="dpdp_privacy_notice_ack">
                    The employee has been given the Privacy Notice explaining what data is collected, why, and their rights. @if ($isCreate)<span class="text-danger">*</span>@endif
                </label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="dpdp_consent_given" id="dpdp_consent_given"
                    value="1" @checked(old('dpdp_consent_given')) @if ($isCreate) required @endif>
                <label class="form-check-label fs-13 text-dark" for="dpdp_consent_given">
                    The employee has given free, specific and informed consent to collect and process their personal data for employment purposes. @if ($isCreate)<span class="text-danger">*</span>@endif
                </label>
            </div>
        </div>
        <div class="col-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="dpdp_sharing_consent" id="dpdp_sharing_consent"
                    value="1" @checked(old('dpdp_sharing_consent'))>
                <label class="form-check-label fs-13 text-dark" for="dpdp_sharing_consent">
                    <span class="text-muted fw-normal">(Optional)</span> The employee consents to sharing their data with third-party processors
                    (payroll, PF/ESI/tax authorities, insurers, background verification agencies).
                </label>
            </div>
        </div>

        <div class="col-md-4">
            <label class="form-label fs-13 fw-semibold text-dark mb-1">
                Consent Obtained Via @if ($isCreate)<span class="text-danger">*</span>@endif
            </label>
            <select name="dpdp_consent_mode" id="dpdp_consent_mode" class="form-select form-control-custom"
                @if ($isCreate) required @endif>
                <option value="">-- Select --</option>
                <option value="Written" @selected(old('dpdp_consent_mode') === 'Written')>Written / Signed Form</option>
                <option value="Electronic" @selected(old('dpdp_consent_mode', $isCreate ? 'Electronic' : '') === 'Electronic')>Electronic (e-sign / email)</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fs-13 fw-semibold text-dark mb-1">
                Consent Date @if ($isCreate)<span class="text-danger">*</span>@endif
            </label>
            <input type="date" name="dpdp_consent_date" id="dpdp_consent_date" class="form-control form-control-custom"
                max="{{ date('Y-m-d') }}" value="{{ old('dpdp_consent_date', date('Y-m-d')) }}"
                @if ($isCreate) required @endif>
        </div>
    </div>
@endif

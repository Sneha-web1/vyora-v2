{{--
    Section 2 of admin/employee/create.blade.php and edit.blade.php (shown right after Personal Details).
    Expects: $employee (null on create)
--}}
@php
    $emp = $employee ?? null;

    // ---- Family rows (old input > saved rows > 2 empty rows) ----
    $familyRows = old('family_members');
    if ($familyRows === null) {
        $familyRows = $emp
            ? $emp->familyMembers->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'phone' => $m->phone,
                'relation' => $m->relation,
            ])->values()->all()
            : [];
    }
    $familyRows = array_values($familyRows);
    while (count($familyRows) < 2) {
        $familyRows[] = ['id' => '', 'name' => '', 'phone' => '', 'relation' => ''];
    }
    $relations = \App\Models\EmployeeFamilyMember::RELATIONS;
@endphp

<div class="form-section-divider"></div>

<!-- SECTION 2: FAMILY DETAILS -->
<div class="employee-section-header justify-content-between">
    <span><i class="ti ti-users text-primary"></i> 2. Family Details <span class="text-danger">*</span></span>
    <button type="button" class="btn btn-sm btn-outline-primary" id="addFamilyRowBtn">
        <i class="ti ti-plus me-1"></i>Add Family Member
    </button>
</div>
<p class="text-muted fs-12 mb-3">Details of at least two family members are mandatory (name, phone number and relation with the employee).</p>

<div id="familyRows" class="d-flex flex-column gap-2">
    @foreach ($familyRows as $i => $row)
        <div class="row g-2 align-items-end family-row border rounded p-2 mx-0" data-index="{{ $i }}">
            <input type="hidden" name="family_members[{{ $i }}][id]" value="{{ $row['id'] ?? '' }}">
            <div class="col-md-4">
                <label class="form-label fs-12 fw-semibold text-dark mb-1">Name <span class="text-danger">*</span></label>
                <input type="text" name="family_members[{{ $i }}][name]" class="form-control form-control-custom"
                    maxlength="150" value="{{ $row['name'] ?? '' }}" placeholder="Full name" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fs-12 fw-semibold text-dark mb-1">Phone Number <span class="text-danger">*</span></label>
                <input type="text" name="family_members[{{ $i }}][phone]" class="form-control form-control-custom"
                    maxlength="20" pattern="[0-9+\-\s()]{7,20}" value="{{ $row['phone'] ?? '' }}"
                    placeholder="e.g. 9876543210" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fs-12 fw-semibold text-dark mb-1">Relation with Employee <span class="text-danger">*</span></label>
                <select name="family_members[{{ $i }}][relation]" class="form-select form-control-custom select2" required>
                    <option value="">-- Select Relation --</option>
                    @foreach ($relations as $rel)
                        <option value="{{ $rel }}" @selected(($row['relation'] ?? '') === $rel)>{{ $rel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 text-end">
                @if ($i >= 2)
                    <button type="button" class="btn btn-outline-danger btn-sm remove-family-row" title="Remove">
                        <i class="ti ti-trash"></i>
                    </button>
                @endif
            </div>
        </div>
    @endforeach
</div>

@push('after_scripts')
    <script>
        (function() {
            function init() {
                var container = document.getElementById('familyRows');
                var addBtn = document.getElementById('addFamilyRowBtn');
                if (!container || !addBtn) return;

                var relations = @json($relations);
                var nextIndex = container.querySelectorAll('.family-row').length;
                // keep indexes unique even if rows were removed earlier
                container.querySelectorAll('.family-row').forEach(function(r) {
                    nextIndex = Math.max(nextIndex, parseInt(r.getAttribute('data-index'), 10) + 1);
                });

                addBtn.addEventListener('click', function() {
                    var i = nextIndex++;
                    var opts = '<option value="">-- Select Relation --</option>' + relations.map(function(r) {
                        return '<option value="' + r + '">' + r + '</option>';
                    }).join('');
                    var div = document.createElement('div');
                    div.className = 'row g-2 align-items-end family-row border rounded p-2 mx-0';
                    div.setAttribute('data-index', i);
                    div.innerHTML =
                        '<input type="hidden" name="family_members[' + i + '][id]" value="">' +
                        '<div class="col-md-4"><label class="form-label fs-12 fw-semibold text-dark mb-1">Name <span class="text-danger">*</span></label>' +
                        '<input type="text" name="family_members[' + i + '][name]" class="form-control form-control-custom" maxlength="150" placeholder="Full name" required></div>' +
                        '<div class="col-md-3"><label class="form-label fs-12 fw-semibold text-dark mb-1">Phone Number <span class="text-danger">*</span></label>' +
                        '<input type="text" name="family_members[' + i + '][phone]" class="form-control form-control-custom" maxlength="20" pattern="[0-9+\\-\\s()]{7,20}" placeholder="e.g. 9876543210" required></div>' +
                        '<div class="col-md-3"><label class="form-label fs-12 fw-semibold text-dark mb-1">Relation with Employee <span class="text-danger">*</span></label>' +
                        '<select name="family_members[' + i + '][relation]" class="form-select form-control-custom" required>' + opts + '</select></div>' +
                        '<div class="col-md-2 text-end"><button type="button" class="btn btn-outline-danger btn-sm remove-family-row" title="Remove"><i class="ti ti-trash"></i></button></div>';
                    container.appendChild(div);
                });

                container.addEventListener('click', function(e) {
                    var btn = e.target.closest('.remove-family-row');
                    if (!btn) return;
                    var row = btn.closest('.family-row');
                    if (container.querySelectorAll('.family-row').length > 2) {
                        row.remove();
                    }
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
    </script>
@endpush

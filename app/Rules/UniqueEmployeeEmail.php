<?php

namespace App\Rules;

use App\Models\Employee;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Replacement for `unique:employees,employee_email` now that the column is encrypted.
 * Compares the blind index instead (soft-deleted rows count, same as the old rule).
 */
class UniqueEmployeeEmail implements ValidationRule
{
    public function __construct(private ?int $ignoreEmployeeId = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = Employee::withTrashed()
            ->whereEmailIs((string) $value)
            ->when($this->ignoreEmployeeId, fn ($q) => $q->where('employee_id', '!=', $this->ignoreEmployeeId))
            ->exists();

        if ($exists) {
            $fail('The email has already been taken.');
        }
    }
}

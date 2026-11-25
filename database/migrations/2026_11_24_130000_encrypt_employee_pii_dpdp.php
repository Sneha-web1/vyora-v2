<?php

use App\Models\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DPDP Act 2023 - encrypt employee email / mobile at rest.
 *
 *  1. widen the columns (ciphertext is far longer than varchar(50/20))
 *  2. add keyed blind-index columns for login / unique-check / search
 *  3. encrypt every existing plain-text row (idempotent - already encrypted rows are skipped)
 *
 * Take a DB backup before running. Keep APP_KEY and PII_INDEX_KEY safe:
 * losing APP_KEY makes the encrypted data unrecoverable.
 */
return new class extends Migration
{
    /**
     * Legacy rows contain '0000-00-00' dates (e.g. retirement_date). ALTER TABLE rebuilds the
     * whole table and strict sql_mode (NO_ZERO_DATE) rejects those rows, so relax the mode for
     * this migration's session only and restore it afterwards.
     */
    private function relaxSqlMode(): string
    {
        $old = DB::selectOne('select @@SESSION.sql_mode as m')->m ?? '';
        DB::statement("SET SESSION sql_mode = ''");

        return $old;
    }

    private function restoreSqlMode(string $old): void
    {
        DB::statement('SET SESSION sql_mode = ?', [$old]);
    }

    public function up(): void
    {
        $old = $this->relaxSqlMode();

        try {
            $this->migrateUp();
        } finally {
            $this->restoreSqlMode($old);
        }
    }

    private function migrateUp(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->text('employee_email')->nullable()->change();
            $table->text('employee_mobile')->nullable()->change();
        });

        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'employee_email_bidx')) {
                $table->char('employee_email_bidx', 64)->nullable()->index();
            }
            if (!Schema::hasColumn('employees', 'employee_mobile_bidx')) {
                $table->char('employee_mobile_bidx', 64)->nullable()->index();
            }
        });

        DB::table('employees')->orderBy('employee_id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                $email  = $this->plain($row->employee_email);
                $mobile = $this->plain($row->employee_mobile);

                DB::table('employees')->where('employee_id', $row->employee_id)->update([
                    'employee_email'       => $email  === null || $email  === '' ? null : Crypt::encryptString($email),
                    'employee_mobile'      => $mobile === null || $mobile === '' ? null : Crypt::encryptString($mobile),
                    'employee_email_bidx'  => Employee::blindIndex($email, 'email'),
                    'employee_mobile_bidx' => Employee::blindIndex($mobile, 'mobile'),
                ]);
            }
        }, 'employee_id');
    }

    public function down(): void
    {
        $old = $this->relaxSqlMode();

        try {
            $this->migrateDown();
        } finally {
            $this->restoreSqlMode($old);
        }
    }

    private function migrateDown(): void
    {
        DB::table('employees')->orderBy('employee_id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                DB::table('employees')->where('employee_id', $row->employee_id)->update([
                    'employee_email'  => $this->plain($row->employee_email),
                    'employee_mobile' => $this->plain($row->employee_mobile),
                ]);
            }
        }, 'employee_id');

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['employee_email_bidx']);
            $table->dropIndex(['employee_mobile_bidx']);
            $table->dropColumn(['employee_email_bidx', 'employee_mobile_bidx']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->string('employee_email', 50)->nullable(false)->change();
            $table->string('employee_mobile', 20)->nullable(false)->change();
        });
    }

    /** Returns the plain value whether the stored value is plain text or already encrypted. */
    private function plain(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return $value; // was plain text
        }
    }
};

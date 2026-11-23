<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            // company form me mandatory hai, par purani rows ke liye DB me nullable
            $table->unsignedBigInteger('company_id')->nullable()->after('policy_code');
            $table->unsignedBigInteger('branch_id')->nullable()->after('company_id');

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');

            // department ab optional hai
            $table->unsignedBigInteger('department_id')->nullable()->change();
        });

        // Purani policies ka company/branch department se bhar do
        DB::table('policies')->whereNotNull('department_id')->orderBy('id')->get()->each(function ($policy) {
            $dept = DB::table('departments')->where('id', $policy->department_id)->first();
            if ($dept) {
                DB::table('policies')->where('id', $policy->id)->update([
                    'company_id' => $dept->company_id,
                    'branch_id' => $dept->branch_id ?? null,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('policies', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['company_id', 'branch_id']);
        });
    }
};

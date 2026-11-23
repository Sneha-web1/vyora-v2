<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('policies')) {
            return;
        }

        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->string('policy_title');
            $table->string('policy_code')->unique();
            $table->unsignedBigInteger('department_id');
            $table->date('effective_from');
            $table->date('expiry_date')->nullable();
            $table->string('policy_document')->nullable();
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};

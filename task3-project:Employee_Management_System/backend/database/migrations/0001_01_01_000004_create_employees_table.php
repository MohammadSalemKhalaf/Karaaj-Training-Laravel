<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('department_id')->constrained('departments')->restrictOnDelete();
            $table->string('employee_code')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone_number');
            $table->text('address')->nullable();
            $table->date('hire_date');
            $table->string('job_title');
            $table->string('employment_type', 32);
            $table->string('gender', 16)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('status', 32)->default('active');
            $table->timestamps();

            $table->index('department_id');
            $table->index('status');
            $table->index('hire_date');
            $table->index('employment_type');
            $table->index('gender');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

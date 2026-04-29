<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('status', 64)->default('submitted');
            $table->decimal('ai_generated_score', 5, 2)->nullable();
            $table->text('ai_generated_feedback')->nullable();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('resume_id')->nullable()->constrained('resumes')->nullOnDelete();
            $table->foreignUuid('job_vacancy_id')->nullable()->constrained('job_vacancies')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('user_id');
            $table->index('job_vacancy_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};

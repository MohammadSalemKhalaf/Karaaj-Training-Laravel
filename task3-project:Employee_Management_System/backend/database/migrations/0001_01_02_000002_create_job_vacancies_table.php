<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_vacancies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('type', 64)->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->foreignUuid('category_id')->nullable()->constrained('job_categories')->nullOnDelete();
            $table->foreignUuid('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->string('status', 32)->default('open');
            $table->timestamps();
            $table->softDeletes();

            $table->index('title');
            $table->index('type');
            $table->index('status');
            $table->index('company_id');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_vacancies');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_vacancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('title', 150);
            $table->string('area', 50);
            $table->text('description');
            $table->string('state', 2);
            $table->string('city', 150);
            $table->string('contact', 150);
            $table->decimal('salary', 10, 2)->nullable();
            $table->timestamps();

            $table->index(['company_id', 'area']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_vacancies');
    }
};

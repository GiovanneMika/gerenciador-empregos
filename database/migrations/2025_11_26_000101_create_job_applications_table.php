<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('job_vacancies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('email', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('education', 600);
            $table->string('experience', 600);
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->unique(['job_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};

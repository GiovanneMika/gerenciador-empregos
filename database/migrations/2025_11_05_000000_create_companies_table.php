<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique(); // Nome da empresa (único)
            $table->string('business', 150); // Ramo da empresa
            $table->string('username', 20)->unique(); // Username único
            $table->string('password'); // Senha criptografada
            $table->string('street', 150); // Rua
            $table->string('number', 8); // Número (máx 8 chars)
            $table->string('city', 150); // Cidade
            $table->string('state', 2); // Estado (ex: PR)
            $table->string('phone', 14); // Telefone (10-14 dígitos)
            $table->string('email', 150); // Email obrigatório
            $table->string('role')->default('company'); // Role sempre 'company'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
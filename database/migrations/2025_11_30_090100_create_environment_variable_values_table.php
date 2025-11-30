<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('environment_variable_values', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('environment_id')->constrained('environments')->cascadeOnDelete();
            $table->foreignId('variable_key_id')->constrained('variable_keys')->cascadeOnDelete();
            $table->text('value');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->constrained('users');
            $table->timestamps();

            $table->unique(['environment_id', 'variable_key_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('environment_variable_values');
    }
};

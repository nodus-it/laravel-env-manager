<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_variable_values', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('variable_key_id')->constrained('variable_keys')->cascadeOnDelete();
            $table->text('value');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->constrained('users');
            $table->timestamps();

            $table->unique(['project_id', 'variable_key_id'], 'proj_var_values_proj_key_uq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_variable_values');
    }
};

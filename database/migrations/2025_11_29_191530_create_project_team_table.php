<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('role');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['project_id', 'team_id']);
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_team');
    }
};

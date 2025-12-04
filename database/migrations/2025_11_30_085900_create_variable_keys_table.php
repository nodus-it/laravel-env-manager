<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variable_keys', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('description')->nullable();
            $table->string('type');
            $table->boolean('is_secret')->default(false);
            $table->text('validation_rules')->nullable();
            $table->text('default_value')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variable_keys');
    }
};

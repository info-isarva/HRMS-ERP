<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique(); // e.g. employees.view
                $table->string('module');
                $table->string('action');
                $table->string('display_name');
                $table->string('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->json('route_names')->nullable();
                $table->string('route_name')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};

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
        Schema::create('attendance_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('shift_threshold_hours', 5, 2)->default(18); // e.g. 18 hours
            $table->integer('recovery_days_offset')->default(2); // e.g. if worked Feb 1, then Feb 3 is impacted
            $table->string('recovery_status')->default('compoff'); // present, absentee, compoff
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_rules');
    }
};

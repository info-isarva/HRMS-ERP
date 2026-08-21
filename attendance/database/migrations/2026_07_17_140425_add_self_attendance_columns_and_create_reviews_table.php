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
        // 1. Add self-attendance details to the existing attendances table if not exists
        Schema::table('attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('attendances', 'check_in_ip')) {
                $table->string('check_in_ip')->nullable();
            }
            if (!Schema::hasColumn('attendances', 'check_out_ip')) {
                $table->string('check_out_ip')->nullable();
            }
            if (!Schema::hasColumn('attendances', 'check_in_latitude')) {
                $table->decimal('check_in_latitude', 10, 8)->nullable();
            }
            if (!Schema::hasColumn('attendances', 'check_in_longitude')) {
                $table->decimal('check_in_longitude', 11, 8)->nullable();
            }
            if (!Schema::hasColumn('attendances', 'check_out_latitude')) {
                $table->decimal('check_out_latitude', 10, 8)->nullable();
            }
            if (!Schema::hasColumn('attendances', 'check_out_longitude')) {
                $table->decimal('check_out_longitude', 11, 8)->nullable();
            }
            if (!Schema::hasColumn('attendances', 'check_in_location_name')) {
                $table->string('check_in_location_name')->nullable();
            }
            if (!Schema::hasColumn('attendances', 'check_out_location_name')) {
                $table->string('check_out_location_name')->nullable();
            }
        });

        // 2. Create the attendance_reviews table if it does not exist
        if (!Schema::hasTable('attendance_reviews')) {
            Schema::create('attendance_reviews', function (Blueprint $table) {
                $table->id();
                $table->string('employee_payroll_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->integer('month');
                $table->integer('year');
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->text('notes')->nullable();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'month', 'year']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_reviews');

        Schema::table('attendances', function (Blueprint $table) {
            $cols = [];
            foreach ([
                'check_in_ip', 'check_out_ip', 'check_in_latitude', 'check_in_longitude',
                'check_out_latitude', 'check_out_longitude', 'check_in_location_name', 'check_out_location_name'
            ] as $col) {
                if (Schema::hasColumn('attendances', $col)) {
                    $cols[] = $col;
                }
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};

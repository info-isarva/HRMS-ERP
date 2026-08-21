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
        Schema::create('financial_year_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('start_month')->default(4); // April = 4, January = 1
            $table->boolean('auto_close_enabled')->default(true); // Auto-close previous FY
            $table->integer('auto_close_days_after')->default(30); // Days after new FY starts to auto-close previous
            $table->boolean('auto_create_next')->default(true); // Auto-create next FY
            $table->integer('create_next_days_before')->default(30); // Days before current FY ends to create next
            $table->json('notification_settings')->nullable(); // Email notifications for FY events
            $table->text('closing_policy')->nullable(); // Policy text for FY closing
            $table->timestamps();
        });
        
        // Insert default settings
        DB::table('financial_year_settings')->insert([
            'start_month' => 4, // April start (Indian FY)
            'auto_close_enabled' => true,
            'auto_close_days_after' => 30,
            'auto_create_next' => true,
            'create_next_days_before' => 30,
            'notification_settings' => json_encode([
                'notify_on_close' => true,
                'notify_on_create' => true,
                'notify_users' => ['admin'],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_year_settings');
    }
};

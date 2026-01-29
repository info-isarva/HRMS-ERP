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
        // Insert Notice Period Duration Setting
        \DB::table('settings')->insert([
            [
                'key' => 'notice_period_duration',
                'display_name' => 'Notice Period Duration (Days)',
                'value' => '30',
                'type' => 'number',
                'description' => 'Default notice period duration in days',
                'group' => 'general', // Or create a new 'exit' group
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'restrict_portal_on_notice',
                'display_name' => 'Restrict Portal Access on Notice',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Restrict employee from accessing the portal during notice period',
                'group' => 'general',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'restrict_leave_on_notice',
                'display_name' => 'Restrict Leave Application on Notice',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Restrict employee from applying for leave during notice period',
                'group' => 'general',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::table('settings')->whereIn('key', [
            'notice_period_duration', 
            'restrict_portal_on_notice', 
            'restrict_leave_on_notice'
        ])->delete();
    }
};

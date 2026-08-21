<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $defaultNewRegime = [
            ['from' => 0, 'to' => 300000, 'percentage' => 0],
            ['from' => 300000, 'to' => 700000, 'percentage' => 5],
            ['from' => 700000, 'to' => 1000000, 'percentage' => 10],
            ['from' => 1000000, 'to' => 1200000, 'percentage' => 15],
            ['from' => 1200000, 'to' => 1500000, 'percentage' => 20],
            ['from' => 1500000, 'to' => null, 'percentage' => 30]
        ];

        $defaultOldRegime = [
            ['from' => 0, 'to' => 250000, 'percentage' => 0],
            ['from' => 250000, 'to' => 500000, 'percentage' => 5],
            ['from' => 500000, 'to' => 1000000, 'percentage' => 20],
            ['from' => 1000000, 'to' => null, 'percentage' => 30]
        ];

        DB::table('settings')->updateOrInsert(
            ['key' => 'salary_tds_enabled'],
            [
                'display_name' => 'Salary Tds Enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'salary',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'salary_tds_slabs_new'],
            [
                'display_name' => 'Salary Tds Slabs New',
                'value' => json_encode($defaultNewRegime),
                'type' => 'json',
                'group' => 'salary',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        DB::table('settings')->updateOrInsert(
            ['key' => 'salary_tds_slabs_old'],
            [
                'display_name' => 'Salary Tds Slabs Old',
                'value' => json_encode($defaultOldRegime),
                'type' => 'json',
                'group' => 'salary',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'salary_tds_enabled',
            'salary_tds_slabs_new',
            'salary_tds_slabs_old'
        ])->delete();
    }
};

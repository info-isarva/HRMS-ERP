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
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('document_name');
            $table->string('short_name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        // Insert default data
        DB::table('document_types')->insert([
            ['document_name' => 'Aadhaar Card', 'short_name' => 'ADHAAR', 'description' => 'Government issued identity card', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['document_name' => 'PAN Card', 'short_name' => 'PAN', 'description' => 'Permanent Account Number card', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['document_name' => 'Passport', 'short_name' => 'PASS', 'description' => 'International travel document', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['document_name' => 'Driving License', 'short_name' => 'DL', 'description' => 'Vehicle driving license', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['document_name' => 'Voter ID', 'short_name' => 'VOTER', 'description' => 'Voter identification card', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['document_name' => 'Education Certificate', 'short_name' => 'EDU', 'description' => 'Educational qualification certificate', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['document_name' => 'Experience Certificate', 'short_name' => 'EXP', 'description' => 'Work experience certificate', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['document_name' => 'Photograph', 'short_name' => 'PHOTO', 'description' => 'Employee photograph', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['document_name' => 'Signature', 'short_name' => 'SIGN', 'description' => 'Employee signature', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['document_name' => 'Resignation Letter', 'short_name' => 'RESIGN', 'description' => 'Employee resignation letter', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['document_name' => 'Other Document', 'short_name' => 'OTHER', 'description' => 'Other miscellaneous documents', 'status' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};

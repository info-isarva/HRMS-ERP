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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('display_name');
            $table->text('value')->nullable();
            $table->enum('type', ['text', 'boolean', 'number', 'json'])->default('text');
            $table->text('description')->nullable();
            $table->string('group')->default('general'); // For grouping settings
            $table->integer('display_order')->default(0); // For ordering in the UI
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('settings')->insert([
            [
                'key' => 'enable_self_portal',
                'display_name' => 'Enable Self Portal',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Global control to enable or disable the self portal feature for employees',
                'group' => 'portal',
                'display_order' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posh_policy_acknowledgements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('posh_policy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('acknowledged_at');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->unique(['posh_policy_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posh_policy_acknowledgements');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenant_wireguard_peers', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('public_key')->unique();
            $table->string('assigned_ip');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_wireguard_peers');
    }
};

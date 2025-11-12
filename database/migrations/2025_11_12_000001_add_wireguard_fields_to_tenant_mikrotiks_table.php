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
        Schema::table('tenant_mikrotiks', function (Blueprint $table) {
            $table->string('wireguard_public_key', 128)->nullable()->after('sync_token');
            $table->string('wireguard_allowed_ips')->nullable()->after('wireguard_public_key');
            $table->string('wireguard_address')->nullable()->after('wireguard_allowed_ips');
            $table->integer('wireguard_port')->nullable()->after('wireguard_address');
            $table->enum('wireguard_status', ['pending', 'active', 'failed'])->default('pending')->after('wireguard_port');
            $table->timestamp('wireguard_last_handshake')->nullable()->after('wireguard_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_mikrotiks', function (Blueprint $table) {
            $table->dropColumn([
                'wireguard_public_key',
                'wireguard_allowed_ips',
                'wireguard_address',
                'wireguard_port',
                'wireguard_status',
                'wireguard_last_handshake',
            ]);
        });
    }
};

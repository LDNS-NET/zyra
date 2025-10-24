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
        Schema::table('users', function (Blueprint $table) {
            // Tenant id stored as string to match tenants.id
            if (!Schema::hasColumn('users', 'tenant_id')) {
                $table->string('tenant_id')->nullable()->after('username')->index();
                // Add foreign key if tenants table exists
                if (Schema::hasTable('tenants')) {
                    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('set null');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'tenant_id')) {
                // Drop foreign key if exists (SQLite may ignore)
                try {
                    $table->dropForeign(['tenant_id']);
                } catch (\Exception $e) {
                    // ignore if foreign key does not exist
                }
                $table->dropColumn('tenant_id');
            }
        });
    }
};

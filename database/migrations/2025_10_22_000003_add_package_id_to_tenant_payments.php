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
        if (!Schema::hasTable('tenant_payments')) {
            return;
        }

        Schema::table('tenant_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('tenant_payments', 'package_id')) {
                $table->unsignedBigInteger('package_id')->nullable()->after('phone');
                // If you want a foreign key and your DB supports it, uncomment below
                // $table->foreign('package_id')->references('id')->on('packages')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('tenant_payments')) {
            return;
        }

        Schema::table('tenant_payments', function (Blueprint $table) {
            if (Schema::hasColumn('tenant_payments', 'package_id')) {
                // If you added a foreign key, drop it first
                // $table->dropForeign(['package_id']);
                $table->dropColumn('package_id');
            }
        });
    }
};

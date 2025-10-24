<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tenant_hotspot_settings', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('portal_template')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('user_prefix')->nullable();
            $table->integer('prune_inactive_days')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('tenant_hotspot_settings');
    }
};

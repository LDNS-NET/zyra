<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tenant_payment_gateways', function (Blueprint $table) {
    $table->id();
    $table->uuid('tenant_id');
    $table->string('provider');
    $table->string('payout_method');
    $table->string('bank_name')->nullable();
    $table->string('bank_account')->nullable();
    $table->string('phone_number')->nullable();
    $table->string('till_number')->nullable();
    $table->string('paybill_business_number')->nullable();
    $table->string('paybill_account_number')->nullable();
    $table->string('label')->nullable();
    $table->boolean('is_active')->default(true);
    $table->softDeletes();
    $table->unsignedBigInteger('created_by')->nullable();
    $table->unsignedBigInteger('last_updated_by')->nullable();
    $table->timestamps();
});

    }

    public function down(): void {
        Schema::dropIfExists('tenant_payment_gateways');
    }
};

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
        Schema::create('tenant_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('network_users'); // If relevant
            $table->string('phone');
            $table->string('receipt_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->timestamp('paid_at')->nullable();
            $table->boolean('checked')->default(false);
            $table->foreignId('created_by')->nullable();
            $table->string('disbursement_type')->nullable();

            // Added for IntaSend STK flow
            $table->foreignId('package_id')->nullable()->constrained('packages')->onDelete('set null');
            $table->string('status')->default('pending');
            $table->string('intasend_reference')->nullable();
            $table->string('intasend_checkout_id')->nullable();
            $table->json('response')->nullable();
            $table->string('transaction_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_payments');
    }
};

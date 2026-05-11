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
        Schema::create('credit_cards', function (Blueprint $table) {
            $table->id();
            $table->string('customer_email')->index();
            $table->string('customer_name');
            $table->string('card_type');
            $table->string('masked_card_number');
            $table->decimal('credit_limit', 12, 2);
            $table->decimal('outstanding_balance', 12, 2);
            $table->decimal('available_credit', 12, 2);
            $table->decimal('minimum_payment_due', 12, 2);
            $table->date('payment_due_date');
            $table->unsignedInteger('reward_points')->default(0);
            $table->string('card_status')->default('Active');
            $table->string('card_reference_number')->nullable();
            $table->timestamp('linked_at')->nullable();
            $table->timestamp('frozen_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_cards');
    }
};

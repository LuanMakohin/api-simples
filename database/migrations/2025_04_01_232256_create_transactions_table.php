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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid();
            $table->foreignId('user_payer_id')->index()->constrained('users')->onDelete('cascade');
            $table->foreignId('user_payee_id')->index()->constrained('users')->onDelete('cascade');
            $table->decimal('value', 10, 2);
            $table->enum('transaction_type', ['deposit', 'transfer']);
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

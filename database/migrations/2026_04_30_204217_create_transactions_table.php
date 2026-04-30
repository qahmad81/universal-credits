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
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('user_id')->indexed()->constrained()->onDelete('cascade');
            $table->foreignId('client_token_id')->nullable()->indexed()->constrained()->onDelete('cascade');
            $table->foreignId('vendor_token_id')->nullable()->indexed()->constrained()->onDelete('cascade');
            $table->enum('type', ['reserve', 'confirm', 'cancel', 'refund', 'topup', 'expire']);
            $table->bigInteger('amount');
            $table->bigInteger('balance_before')->unsigned();
            $table->bigInteger('balance_after')->unsigned();
            $table->string('reference_id')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->nullable();
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

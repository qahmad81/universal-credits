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
        Schema::create('pending_payments', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('client_token_id')->indexed()->constrained()->onDelete('cascade');
            $table->foreignId('vendor_token_id')->indexed()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->indexed()->constrained()->onDelete('cascade');
            $table->bigInteger('amount')->unsigned();
            $table->timestamp('expires_at')->indexed();
            $table->timestamp('created_at')->nullable();
            $table->text('notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_payments');
    }
};

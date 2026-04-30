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
        Schema::table('pending_payments', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('amount');
            $table->renameColumn('notes', 'description');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('pending_payment_id')->nullable()->after('vendor_token_id')->constrained()->onDelete('set null');
            $table->string('description')->nullable()->after('balance_after');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pending_payment_id');
            $table->dropColumn('description');
        });

        Schema::table('pending_payments', function (Blueprint $table) {
            $table->renameColumn('description', 'notes');
            $table->dropColumn('status');
        });
    }
};

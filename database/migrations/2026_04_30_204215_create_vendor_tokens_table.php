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
        Schema::create('vendor_tokens', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('token_hash', 64)->unique()->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('rate_limit_per_minute')->default(60);
            $table->boolean('is_active')->default(true);
            $table->string('webhook_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_tokens');
    }
};

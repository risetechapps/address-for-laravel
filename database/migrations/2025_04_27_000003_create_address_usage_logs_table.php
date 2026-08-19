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
        Schema::create('address_usage_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('address_id')->constrained('addresses')->onDelete('cascade');
            $table->string('action')->comment('delivery, billing, shipping, etc');
            $table->json('metadata')->nullable()->comment('order_id, invoice_id, etc');
            $table->uuid('user_id')->nullable();
            $table->timestamps();

            $table->index('address_id');
            $table->index('action');
            $table->index('created_at');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('address_usage_logs');
    }
};

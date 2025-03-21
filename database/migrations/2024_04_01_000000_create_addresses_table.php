<?php

use Illuminate\Database\Migrations\Migration;
use Tpetry\PostgresqlEnhanced\Schema\Blueprint;
use Tpetry\PostgresqlEnhanced\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::createExtensionIfNotExists('citext');

        Schema::create('addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('address');
            $table->string('zip_code', 20)->nullable();
            $table->caseInsensitiveText('country', 20)->nullable();
            $table->caseInsensitiveText('state', 20)->nullable();
            $table->caseInsensitiveText('city', 100)->nullable();
            $table->caseInsensitiveText('district', 100)->nullable();
            $table->caseInsensitiveText('address', 255)->nullable();
            $table->string('number', 50)->nullable();
            $table->string('complement', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('zip_code');
            $table->index('country');
            $table->index('state');
            $table->index('city');
            $table->index('district');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};

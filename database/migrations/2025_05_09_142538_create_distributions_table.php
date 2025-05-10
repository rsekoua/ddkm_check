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
        Schema::disableForeignKeyConstraints();

        Schema::create('distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->nullable()->constrained();
            $table->foreignId('site_id')->constrained();
            $table->foreignId('delivery_type_id')->constrained();
            $table->dateTime('delivery_date');
            $table->text('difficulties')->nullable();
            $table->text('solutions')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributions');
    }
};

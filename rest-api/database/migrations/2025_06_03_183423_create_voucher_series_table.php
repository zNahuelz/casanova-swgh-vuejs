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
        Schema::create('voucher_series', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('voucher_type')->nullable(false);
            $table->foreign('voucher_type')->references('id')->on('voucher_types');
            $table->string('serie',10);
            $table->integer('next_correlative');
            $table->boolean('is_active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_series');
    }
};

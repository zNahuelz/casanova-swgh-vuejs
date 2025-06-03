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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->double('subtotal');
            $table->double('igv');
            $table->double('total');
            $table->double('change')->default(0);
            $table->boolean('paid');
            $table->string('set',15); //Serie de Boleta. B604
            $table->string('correlative',15); //Correlativo. 008069
            $table->unsignedBigInteger('voucher_type');
            $table->foreign('voucher_type')->references('id')->on('voucher_types');
            $table->unsignedBigInteger('patient_id');
            $table->foreign('patient_id')->references('id')->on('patients');
            $table->unsignedBigInteger('payment_type_id');
            $table->foreign('payment_type_id')->references('id')->on('payment_types');
            $table->string('payment_serial')->nullable(); //Hash que arroja POS o Yape Id.
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};

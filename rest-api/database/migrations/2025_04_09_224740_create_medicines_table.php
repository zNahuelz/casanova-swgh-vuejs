<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name',100);
            $table->string('composition',100);
            $table->string('description',150);
            $table->string('barcode',30);
            $table->double('buy_price');
            $table->double('sell_price');
            $table->double('igv');
            $table->double('profit');
            $table->integer('stock');
            $table->boolean('salable')->default(false); //Vendible SI/NO
            $table->unsignedBigInteger('presentation');
            $table->foreign('presentation')->references('id')->on('presentations');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        DB::statement('ALTER SEQUENCE medicines_id_seq INCREMENT BY 10 START WITH 100');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};

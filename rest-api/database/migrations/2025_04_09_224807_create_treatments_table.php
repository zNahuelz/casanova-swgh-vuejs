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
        Schema::create('treatments', function (Blueprint $table) {
            $table->id();
            $table->string('name',100);
            $table->string('description')->default('---');
            $table->string('procedure')->default('---');
            $table->double('price');
            $table->softDeletes();
            $table->timestamps();
        });
        DB::statement('ALTER SEQUENCE treatments_id_seq INCREMENT BY 10 START WITH 100');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treatments');
    }
};

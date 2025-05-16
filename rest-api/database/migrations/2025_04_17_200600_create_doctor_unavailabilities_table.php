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
        Schema::create('doctor_unavailabilities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id');
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->string('reason',20)->default('VACACIONES');
            $table->foreign('doctor_id')->references('id')->on('doctors');
            $table->softDeletes();
            $table->timestamps();
        });
        DB::statement("ALTER TABLE doctor_unavailabilities ADD CONSTRAINT unavailabilities_reason_check CHECK(reason IN('VACACIONES','ENFERMEDAD','DIA LIBRE','MATERNIDAD','NO ESPECIFICADO'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_unavailabilities');
        DB::statement('DROP TYPE IF EXISTS unavailabilities_reason_check');
    }
};

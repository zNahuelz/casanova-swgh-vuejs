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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('time');
            $table->string('notes',150);
            $table->string('status',20)->default('PENDIENTE'); // 0 PENDIENTE - 1 ATENTIDO - 2 REASIGNADO
            $table->boolean('is_remote')->default(false);
            $table->integer('duration');
            $table->date('rescheduling_date')->nullable();
            $table->time('rescheduling_time')->nullable();
            $table->unsignedBigInteger('is_treatment')->nullable();
            $table->foreign('is_treatment')->references('id')->on('treatments');
            $table->unsignedBigInteger('patient_id');
            $table->foreign('patient_id')->references('id')->on('patients');
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->foreign('doctor_id')->references('id')->on('doctors');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        DB::statement('ALTER SEQUENCE appointments_id_seq INCREMENT BY 10 START WITH 100');
        DB::statement("ALTER TABLE appointments ADD CONSTRAINT appointments_status_check CHECK(status IN('PENDIENTE','ATENDIDO','REPROGRAMADO','CANCELADO'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
        DB::statement('DROP TYPE IF EXISTS appointment_status_check');
    }
};

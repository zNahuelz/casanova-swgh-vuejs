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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('name',30);
            $table->string('paternal_surname',30);
            $table->string('maternal_surname',30);
            $table->string('dni',15)->unique();
            $table->string('email',50)->default('EMAIL@DOMINIO.COM');
            $table->string('phone',15)->default('000000000');
            $table->string('address',100)->default('---');
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        DB::statement('ALTER SEQUENCE patients_id_seq INCREMENT BY 10 START WITH 100');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};

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
        Schema::create('presentations', function (Blueprint $table) {
            $table->id();
            $table->string('name',50); //Capsulas Masticables
            $table->double('numeric_value'); //50
            $table->string('aux',20)->nullable(); //Unidades --- TODO: Opcional?
            $table->unique(['name', 'numeric_value', 'aux'], 'unique_presentation');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presentations');
    }
};

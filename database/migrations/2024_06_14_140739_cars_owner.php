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
        Schema::create('cars_owners', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('active');
            $table->string('email')->unique();
            $table->string('sex');
            $table->string('age');
            $table->string('adress');
            $table->integer('number_services')->nullable();
            $table->timestamps();
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

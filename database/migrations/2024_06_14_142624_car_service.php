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
        Schema::create('car_service', function (Blueprint $table) {
            $table->id();
            $table->json('name_service');
            $table->decimal('price', 10, 2);
            $table->unsignedBigInteger('car_id');
            $table->foreign('car_id')->references('id')->on('cars');
            $table->unsignedBigInteger('owner_id');
            $table->foreign('owner_id')->references('id')->on('cars_owners');
            $table->unsignedBigInteger('worker_id');
            $table->foreign('worker_id')->references('id')->on('workers');
            $table->date('date_service');
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

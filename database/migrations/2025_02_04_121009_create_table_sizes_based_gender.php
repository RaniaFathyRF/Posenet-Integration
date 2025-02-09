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
        Schema::create('sizes_gender', function (Blueprint $table) {
            $table->id();
            $table->string('gender');  
            $table->integer('height_min');
            $table->integer('height_max');
            $table->integer('weight_min');
            $table->integer('weight_max');
            $table->string('size');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sizes_gender');
    }
};

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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('summary');
            $table->text('information');
            $table->text('description');
            $table->string('lat');
            $table->string('long');
            $table->string('address');
            $table->string('amount');
            $table->string('link');
            $table->string('email');
            $table->string('phone');
            $table->string('website');
            $table->string('facebook');
            $table->string('instagram');
            $table->string('youtube');
            $table->string('whatsapp');
            $table->tinyInteger('vip')->default(0);
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('priority')->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('image',2048);
            $table->string('slider_image',2048);
            $table->string('video',2048)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('point')->default(0);
            $table->tinyInteger('rate')->default(0);
            $table->string('lat');
            $table->string('long');
            $table->text('website')->nullable();
            $table->text('facebook')->nullable();
            $table->text('instagram')->nullable();
            $table->text('youtube')->nullable();
            $table->text('tiktok')->nullable();
            $table->text('whatsapp')->nullable();
            $table->text('phone')->nullable();
            $table->text('email')->nullable();
            $table->text('address')->nullable();
            $table->decimal('start_amount', 15, 2);
            $table->tinyInteger('amount_type')->default(0);
            $table->string('image', 2048)->nullable();
            $table->string('menu_image', 2048)->nullable();
            $table->string('slider_image', 2048)->nullable();
            $table->string('video', 2048)->nullable();
            $table->tinyInteger('from_monday')->default(0);
            $table->tinyInteger('from_tuesday')->default(0);
            $table->tinyInteger('from_wednesday')->default(0);
            $table->tinyInteger('from_thursday')->default(0);
            $table->tinyInteger('from_friday')->default(0);
            $table->tinyInteger('from_saturday')->default(0);
            $table->tinyInteger('from_sunday')->default(0);
            $table->tinyInteger('to_monday')->default(0);
            $table->tinyInteger('to_tuesday')->default(0);
            $table->tinyInteger('to_wednesday')->default(0);
            $table->tinyInteger('to_thursday')->default(1);
            $table->tinyInteger('to_friday')->default(0);
            $table->tinyInteger('to_saturday')->default(0);
            $table->tinyInteger('to_sunday')->default(0);
            $table->tinyInteger('active')->default(0);
            $table->enum('status',['pending', 'approved'])->default('pending'); // pending, approved
            $table->tinyInteger('vip')->default(0);
            $table->tinyInteger('priority')->default(0);
            $table->bigInteger('country_id')->unsigned()->index();
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->bigInteger('city_id')->unsigned()->index();
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            $table->bigInteger('area_id')->unsigned()->index();
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('cascade');
            $table->bigInteger("user_id")->unsigned()->index();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
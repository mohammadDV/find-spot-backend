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
        Schema::create('review_files', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('review_id')->unsigned()->index();
            $table->foreign('review_id')->references('id')->on('reviews')->onDelete('cascade');
            $table->string('path');
            $table->enum('type', ['image', 'video', 'document'])->nullable(); // image, video,
            $table->tinyInteger('status')->default(1); // 0 = inactive, 1 = active
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('review_files');
    }
};

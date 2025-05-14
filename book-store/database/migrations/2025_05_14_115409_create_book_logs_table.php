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
        Schema::create('book_logs', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('book_id');
    $table->string('action'); // 'created', 'updated', 'deleted'
    $table->text('details')->nullable();
    $table->timestamps();
    
    $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_logs');
    }
};

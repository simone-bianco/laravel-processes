<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processes', function (Blueprint $table) {
            $table->id();

            $table->string('processable_type');
            $table->string('processable_id');
            $table->index(['processable_type', 'processable_id']);

            $table->string('status', 50)->default('pending');
            $table->string('type');
            $table->text('error')->nullable();

            $table->json('context')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processes');
    }
};

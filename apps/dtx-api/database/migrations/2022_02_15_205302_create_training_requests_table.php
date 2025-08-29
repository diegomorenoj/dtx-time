<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainingRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('training_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50);
            $table->string('name', 50);
            $table->string('shortname', 255)->nullable();
            $table->string('institute', 255)->nullable();
            $table->string('category', 50)->nullable();
            $table->integer('hours');
            $table->timestamp('start_date')->useCurrent();
            $table->timestamp('end_date')->useCurrent();
            $table->string('permission', 50)->nullable();
            $table->string('schedule', 50)->nullable();
            $table->string('methodology', 50)->nullable();
            $table->longText('comments')->nullable();
            $table->string('fee', 50)->nullable();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('specialty_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('training_requests');
    }
}

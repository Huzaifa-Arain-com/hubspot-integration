<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hs_object_id')->unique()->nullable();
            $table->string('deal_name', 50)->nullable();
            $table->string('pipeline', 50)->nullable();
            $table->string('deal_stage', 50)->nullable();
            $table->unsignedDouble('amount', 11, 2)->nullable();
            $table->dateTime('synched_at')->nullable();
            $table->dateTime('failed_at')->nullable();
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
        Schema::dropIfExists('deals');
    }
}

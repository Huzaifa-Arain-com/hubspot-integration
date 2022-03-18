<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealLineItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deal_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained('deals');
            $table->unsignedBigInteger('hs_object_id')->unique()->nullable();
            $table->foreignId('product_id')->constrained('products');
            $table->unsignedInteger('quantity');
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
        Schema::dropIfExists('deal_line_items');
    }
}

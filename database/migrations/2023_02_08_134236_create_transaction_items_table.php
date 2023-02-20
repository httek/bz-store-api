<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id')->index();
            $table->unsignedBigInteger('store_id')->index();
            $table->unsignedBigInteger('goods_id')->index();
            $table->unsignedInteger('sale_price')->default(0)->comment('售出商品单价');
            $table->unsignedTinyInteger('total')->default(1);
            $table->unsignedTinyInteger('review')->default(0);
            $table->json('goods')->nullable();
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
        Schema::dropIfExists('transaction_items');
    }
}

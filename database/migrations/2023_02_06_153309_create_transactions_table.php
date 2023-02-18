<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('root')->default(0);
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('store_id')->index();
            $table->unsignedBigInteger('address_id')->index();
            $table->char('trade_no', 64)->unique();
            $table->char('purchaser', 64)->nullable();
            $table->char('purchaser_phone', 15)->nullable();
            $table->unsignedTinyInteger('status')->default(0)->index()->comment('0 已取消 1 待支付，2 待发货 3 待收货 4 待评价');
            $table->integer('amount')->default(0)->comment('订单金额');
            $table->integer('discount_amount')->default(0)->comment('折扣总额');
            $table->integer('express_amount')->default(0)->comment('运费');
            $table->char('paid_trade_no', 120)->nullable()->unique();
            $table->integer('paid_amount')->default(0)->comment('实际支付金额');
            $table->unsignedTinyInteger('paid_channel')->default(0)->comment('0 微信支付 1 电子卡支付');
            $table->unsignedTinyInteger('paid_type')->default(0)->comment('0 在线支付 1 线下支付');
            $table->timestamp('paid_at')->nullable();
            $table->string('mark', 200)->nullable()->comment('备注');
            $table->unsignedTinyInteger('refundable')->default(1)->comment('是否支付退款');
            $table->unsignedTinyInteger('maintain')->default(0)->comment('是否存在售后');
            $table->json('express')->nullable()->comment('配送信息');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}

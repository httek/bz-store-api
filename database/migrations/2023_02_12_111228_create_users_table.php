<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->char('name', 60)->nullable();
            $table->char('mobile', 15)->nullable()->index();
            $table->unsignedBigInteger('droplet')->default(0);
            $table->string('password')->nullable();
            $table->string('avatar', 400)->nullable();
            $table->char('openid', 64)->nullable()->index();
            $table->char('unionid', 64)->nullable()->index();
            $table->unsignedTinyInteger('status')->default(1);
            $table->unsignedTinyInteger('src')->default(0);
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
        Schema::dropIfExists('users');
    }
}

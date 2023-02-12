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
            $table->char('phone', 15)->nullable();
            $table->unsignedBigInteger('droplet')->default(0);
            $table->string('password')->nullable();
            $table->string('avatar', 400)->nullable();
            $table->char('open_id', 64)->nullable();
            $table->char('union_id', 64)->nullable();
            $table->unsignedTinyInteger('status')->default(0);
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

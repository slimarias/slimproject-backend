<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManufacturersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopping__manufacturers', function (Blueprint $table) {
            $table->id();

            $table->text('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->bigInteger('sort_order')->default(0);
            $table->tinyInteger('status')->default(1)->unsigned();

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
        Schema::dropIfExists('shopping__manufacturers');
    }
}

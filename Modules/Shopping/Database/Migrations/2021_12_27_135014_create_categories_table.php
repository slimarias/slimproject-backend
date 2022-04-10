<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopping__categories', function (Blueprint $table) {
            $table->id();

            $table->text('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->bigInteger('parent_id')->default(0);
            $table->tinyInteger('show_menu')->default(0)->unsigned();
            $table->integer('sort_order')->default(0);
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
        Schema::dropIfExists('shopping__categories');
    }
}

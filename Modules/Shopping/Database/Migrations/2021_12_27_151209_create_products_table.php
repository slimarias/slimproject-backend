<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopping__products', function (Blueprint $table) {
            $table->id();

            $table->text('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('sku')->nullable();
            $table->bigInteger('quantity')->default(0)->unsigned();
            $table->double('price', 30, 2)->default(0);
            $table->double('discount', 30, 2)->default(0);
            $table->date('date_available')->nullable();
            $table->bigInteger('added_by_id')->unsigned()->nullable();
            $table->bigInteger('category_id')->unsigned()->nullable();
            $table->bigInteger('parent_id')->default(0)->unsigned();
            $table->bigInteger('manufacturer_id')->unsigned()->nullable();
            $table->text('related_ids')->nullable();
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
        Schema::dropIfExists('shopping__products');
    }
}

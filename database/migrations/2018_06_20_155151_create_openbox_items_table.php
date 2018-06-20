<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpenboxItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('openbox_items', function (Blueprint $table) {
            $table->increments('id');

            $table->string('product_id');
            $table->string('sku');
            $table->string('product_description');
            $table->string('product_link');
            $table->decimal('product_price', 22, 8);
            $table->decimal('product_original_price', 22, 8)->nullable();

            $table->string('openbox_id')->index();
            $table->decimal('openbox_price', 22, 8);

            $table->string('store_number');
            $table->string('mpn')->nullable();
            $table->string('brand')->nullable();


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
        Schema::dropIfExists('openbox_items');
    }
}

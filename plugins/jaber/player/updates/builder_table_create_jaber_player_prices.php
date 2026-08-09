<?php

namespace Jaber\Player\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateJaberPlayerPrices extends Migration
{
    public function up()
    {
        Schema::create('jaber_player_prices', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->double('price', 10, 0);
            $table->boolean('status')->default(false);
            $table->integer('product_id')->unsigned()->index();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
       
            $table->foreign('product_id')
                ->references('id')
                ->on('jaber_player_products');
        });
    }

    public function down()
    {
        Schema::dropIfExists('jaber_player_prices');
    }
}

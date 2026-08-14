<?php namespace Jaber\Player\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateJaberPlayerInvoices extends Migration
{
    public function up()
    {
        Schema::create('jaber_player_invoices', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('product_id')->unsigned()->index();
            $table->integer('price_id')->unsigned()->index();
            $table->double('number', 10, 0);
            $table->double('total_price', 10, 0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('product_id')
                          ->references('id')
                          ->on('jaber_player_products');
            $table->foreign('price_id')
                          ->references('id')
                          ->on('jaber_player_prices');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('jaber_player_invoices');
    }
}

<?php namespace Jaber\Player\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateJaberPlayerProducts extends Migration
{
    public function up()
    {
        Schema::create('jaber_player_products', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name')->unique()->index();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('jaber_player_products');
    }
}

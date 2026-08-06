<?php namespace Finance\Finance\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateFinanceFinanceMonths extends Migration
{
    public function up()
    {
        Schema::create('finance_finance_months', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->enum('name', [
                '1', '2', '3', '4', '5', '6',
                '7', '8', '9', '10', '11', '12'
            ])->index();
            $table->integer('year_id')->unsigned()->index();
            $table->boolean('status')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('year_id')->references('id')->on('finance_finance_years')
                        						->onDelete('cascade')->onUpdate('cascade');
            $table->unique(['name', 'year_id']);
            $table->integer('user_id')->nullable()->unsigned()->index()->default(null);
            $table->foreign('user_id')
                    ->references('id')
                    ->on('backend_users')
                    ->onDelete('set null')->onUpdate('cascade');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('finance_finance_months');
    }
}

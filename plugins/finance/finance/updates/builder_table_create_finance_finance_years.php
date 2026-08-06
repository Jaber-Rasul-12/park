<?php namespace Finance\Finance\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateFinanceFinanceYears extends Migration
{
    public function up()
    {
        Schema::create('finance_finance_years', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name')->unique()->index();
            $table->boolean('status')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('user_id')->nullable()->unsigned()->index()->default(null);
            $table->foreign('user_id')
                    ->references('id')
                    ->on('backend_users')
                    ->onDelete('set null')->onUpdate('cascade');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('finance_finance_years');
    }
}

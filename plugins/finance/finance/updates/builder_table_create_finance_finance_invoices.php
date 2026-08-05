<?php namespace Finance\Finance\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;

class BuilderTableCreateFinanceFinanceInvoices extends Migration
{
    public function up()
    {
        Schema::create('finance_finance_invoices', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('type');
            $table->string('payment_from');
            $table->string('payment_to');
            $table->string('currency');
            $table->double('amount', 10, 0);
            $table->string('disbursement_statement');
            $table->text('uuid');
            $table->integer('type_id')->unsigned();
            $table->integer('center_id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('type_id')
                        ->references('id')
                        ->on('finance_finance_types')
                        ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('center_id')
                        ->references('id')
                        ->on('finance_finance_centers')
                        ->onDelete('cascade')->onUpdate('cascade');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('finance_finance_invoices');
    }
}

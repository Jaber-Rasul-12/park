<?php

use Illuminate\Support\Facades\Route;

Route::prefix('api')->namespace('Jaber\Player\Http')->group(function () {
    
    // التحقق من وجود فاتورة اليوم
    Route::get('invoice/check-today', 'InvoiceApiController@checkToday');
    
    // عمليات CRUD
    Route::post('invoice', 'InvoiceApiController@store');
    Route::get('invoices', 'InvoiceApiController@index');
    Route::get('invoice/{id}', 'InvoiceApiController@show');
    Route::put('invoice/{id}', 'InvoiceApiController@update');
    Route::delete('invoice/{id}', 'InvoiceApiController@destroy');
});
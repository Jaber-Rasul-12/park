<?php

use Illuminate\Support\Facades\Route;

Route::prefix('api')->namespace('Jaber\Player\Http')->group(function () {
    
    // =========================================================
    // Routes للمنتجات (Products)
    // =========================================================
    Route::get('products', 'ProductApiController@index');
    Route::post('product', 'ProductApiController@store');
    Route::get('product/{id}', 'ProductApiController@show');
    Route::put('product/{id}', 'ProductApiController@update');
    Route::delete('product/{id}', 'ProductApiController@destroy');
    
    // Routes إضافية للمنتجات
    Route::get('product/{id}/prices', 'ProductApiController@getPrices');
    Route::get('product/{id}/invoices', 'ProductApiController@getInvoices');
    
    // =========================================================
    // Routes للفواتير (Invoices)
    // =========================================================
    Route::get('invoices', 'InvoiceApiController@index');
    Route::post('invoice', 'InvoiceApiController@store');
    Route::get('invoice/{id}', 'InvoiceApiController@show');
    Route::put('invoice/{id}', 'InvoiceApiController@update');
    Route::delete('invoice/{id}', 'InvoiceApiController@destroy');
    Route::get('invoice/check-today', 'InvoiceApiController@checkToday');
    
    Route::get('invoices/check-by-date', 'InvoiceApiController@checkInvoicesByDate');
    
    // التحقق من وجود فاتورة لمنتج معين في تاريخ محدد
    Route::get('invoice/check-by-date', 'InvoiceApiController@checkByDate');
});
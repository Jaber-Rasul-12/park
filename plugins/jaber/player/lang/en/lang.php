<?php

return [
    'plugin' => [
        'name' => 'Player',
        'description' => '',
        'player' => 'Player',
        'tables' => 'Tables',
        'categories' => 'Categories',
        'products' => 'Products',
        'prices' => 'Prices',
        'invoices' => 'Invoices',
        'invoice_items' => 'Invoice Items',
        'select' => 'Select',
        'unknown' => 'Unknown',
        'create_and_print' => 'Create and Print',
        'update_and_print' => 'Update and Print',
        'print' => 'Print',
        'signature' => 'Signature',
        'sign_here' => 'Sign here',
        'clear' => 'Clear',
        'your_name' => 'Your name',
        'name_conversion' => 'Name conversion',
        'date' => 'date',
        'add' => 'Add',
        'remove' => 'Remove',
        'amount' => 'Amount',
        'reports' => 'Reports',
        'from_date' => 'From date',
        'to_date' => 'To date',
        'total_price' => 'Total price',
        'message_delete' => 'Cannot be deleted because there is data associated with the section',
        'price_require' => 'Price required',
        'error_status_save' => 'The name must be unique.',
        'create_and_new' => 'Create and new',
    ],
    'model' => [

        'product' => [
            'id' => 'Id',
            'price' => 'Price',
            'name' => 'Name',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
        ],

        'price' => [
            'id' => 'Id',
            'status' => 'Status',
            'product' => 'Product',
            'price' => 'Price',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
        ],
        'invoice' => [
            'id' => 'Id',
            'product' => 'Product',
            'invoice' => 'Invoice',
            'category' => 'Category',
            'price' => 'Price',
            'number' => 'Number',
            'total_price' => 'Total price',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
        ],
    ],
    'controller' => [
      
        'products' => [
            'products' => 'Products',
        ],
        'prices' => [
            'prices' => 'Prices',
        ],
        'invoices' => [
            'invoices' => 'Invoices',
        ],
    ],
];

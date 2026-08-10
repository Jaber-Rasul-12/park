<?php

namespace Jaber\Player\Controllers;

use Jaber\Player\Models\Invoice;
use Backend\Classes\Controller;
use BackendMenu;
use Jaber\Player\Models\Product;
use Flash;

class Invoices extends Controller
{
    public $implement = ['Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'invoices'
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Jaber.Player', 'player', 'invoices');
    }




               public function formGetRedirectUrl($context = null, $model = null)
    {
               $url = post('url');
        if (($url == 'create') && !empty($url)) {
            return "jaber/player/invoices/create";
        }else if (($url == 'preview') && !empty($url)) {
            return "jaber/player/invoices/$url/$model->id";
        }else {
            if ((post("close") == 1) && !empty(post("close"))) {
                return "jaber/player/invoices";
            } else {
                return "jaber/player/invoices/update/$model->id";
            }
        }
    }

    public function preview($id, $context = null)
    {
        $this->addCss('/plugins/jaber/player/assets/filesignatore/jquery.signaturepad.css', 'Jaber.Player');
        $this->addJs('/plugins/jaber/player/assets/filesignatore/jquery.signaturepad.js', 'Jaber.Player');
        $this->addJs('/plugins/jaber/player/assets/filesignatore/json2.min.js', 'Jaber.Player');
        return $this->asExtension('FormController')->preview($id, $context);
    }


public function invoices()
{
    $this->pageTitle = trans('jaber.player::lang.plugin.invoice_speed');
    // جلب جميع المنتجات مع السعر النشط من جدول prices
    $products = Product::with(['prices' => function($query) {
        $query->where('status', true)->first();
    }])->get();
    
    // معالجة كل منتج لإضافة السعر النشط
    $products->each(function($product) {
        $activePrice = $product->prices()->where('status', true)->first();
        $product->active_price = $activePrice ? $activePrice->price : 0;
        $product->price_id = $activePrice ? $activePrice->id : null;
    });
    
    $this->vars['invoices'] = $products;
}

       public function onPrintInvoice()
    {
        // الحصول على البيانات
        $invoiceData = post('invoice_data');
        $invoiceData = json_decode($invoiceData, true);
        
        // التحقق من صحة البيانات
        if (!$invoiceData || !isset($invoiceData['items']) || empty($invoiceData['items'])) {
            return [
                'success' => false, 
                'message' => 'بيانات الفاتورة غير صحيحة'
            ];
        }

        try {
            // ====== حفظ كل منتج كفاتورة منفصلة ======
            $savedInvoices = [];
            
            foreach ($invoiceData['items'] as $item) {
                $invoice = new Invoice();
                $invoice->product_id = $item['product_id'];
                $invoice->price_id = $item['price_id'];
                $invoice->number = $item['quantity'];
                $invoice->total_price = $item['subtotal'];
                $invoice->created_at = now();
                $invoice->updated_at = now();
                $invoice->save();
                
                $savedInvoices[] = $invoice->id;
            }
            
            // ====== تجهيز بيانات الطباعة ======
            $this->vars['items'] = $invoiceData['items'];
            $this->vars['total'] = $invoiceData['total'];
            $this->vars['invoice_ids'] = implode(', ', $savedInvoices);
            $this->vars['created_at'] = now()->format('d/m/Y h:i A');
            
            // ====== إرجاع البيانات ======
            return [
                'success' => true,
                'message' => 'تم إنشاء الفواتير بنجاح',
                'invoice_ids' => $savedInvoices,
                'html' => $this->makePartial('print_invoice', [
                    'items' => $invoiceData['items'],
                    'total' => $invoiceData['total'],
                    'invoice_ids' => implode(', ', $savedInvoices),
                    'created_at' => now()->format('d/m/Y h:i A')
                ])
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ];
        }
    }

    

}

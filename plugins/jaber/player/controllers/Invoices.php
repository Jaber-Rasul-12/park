<?php

namespace Jaber\Player\Controllers;

use Jaber\Player\Models\Invoice;
use Backend\Classes\Controller;
use BackendMenu;
use Jaber\Player\Models\Product;
use Flash;
use DB;
use Ramsey\Uuid\Uuid;

use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;
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



// public function onPrintInvoice()
// {
//     $invoiceData = post('invoice_data');
//     $invoiceData = json_decode($invoiceData, true);
    
//     if (!$invoiceData || !isset($invoiceData['items']) || empty($invoiceData['items'])) {
//         return [
//             'success' => false, 
//             'message' => 'بيانات الفاتورة غير صحيحة'
//         ];
//     }

//     try {
//         $savedInvoices = [];
//         $receiptsHtml = []; // مصفوفة لتخزين كل إيصال على حدة
        
//         // ====== لكل منتج في الفاتورة ======
//         foreach ($invoiceData['items'] as $item) {
//             $quantity = (int)$item['quantity'];
            
//             // ====== طباعة إيصال منفصل لكل قطعة ======
//             for ($i = 0; $i < $quantity; $i++) {
//                 // حفظ الفاتورة في قاعدة البيانات
//                 $invoice = new Invoice();
//                 $invoice->product_id = $item['product_id'];
//                 $invoice->price_id = $item['price_id'];
//                 $invoice->number = 1;
//                 $invoice->total_price = $item['price'];
//                 $invoice->created_at = now();
//                 $invoice->updated_at = now();
//                 $invoice->save();
//                 $savedInvoices[] = $invoice->id;
                
//                 // ====== توليد HTML لكل إيصال على حدة ======
//                 $receiptHtml = $this->makePartial('print_invoice', [
//                     'item' => $item,
//                     'copy_number' => $i + 1,
//                     'total_copies' => $quantity,
//                     'invoice_id' => $invoice->id,
//                     'created_at' => now()->format('d/m/Y h:i A')
//                 ]);
                
//                 $receiptsHtml[] = $receiptHtml; 
//             }
//         }
        
//         return [
//             'success' => true,
//             'message' => 'تم إنشاء الفواتير بنجاح',
//             'invoice_ids' => $savedInvoices,
//             'receipts' => $receiptsHtml // إرسال المصفوفة بدلاً من نص واحد
//         ];
        
//     } catch (\Exception $e) {
//         return [
//             'success' => false,
//             'message' => 'حدث خطأ: ' . $e->getMessage()
//         ];
//     }
// }


public function onPrintInvoice()
{
    $invoiceData = post('invoice_data');
    $invoiceData = json_decode($invoiceData, true);
    
    if (!$invoiceData || !isset($invoiceData['items']) || empty($invoiceData['items'])) {
        return [
            'success' => false, 
            'message' => 'بيانات الفاتورة غير صحيحة'
        ];
    }

    try {
        // ====== عدد النسخ ثابت = 1 (يمكنك تغييره هنا مباشرة) ======
        $printCopies = 1; // غيّر هذا الرقم إلى عدد النسخ الذي تريده
        
        $savedInvoices = [];
        $receiptsHtml = [];
        
        // ====== لكل منتج في الفاتورة ======
        foreach ($invoiceData['items'] as $item) {
            $quantity = (int)$item['quantity'];
            
            // ====== حفظ فاتورة واحدة فقط لكل منتج ======
            $invoice = new Invoice();
            $invoice->product_id = $item['product_id'];
            $invoice->price_id = $item['price_id'];
            $invoice->number = $quantity; 
            $invoice->uuid = 'azadi-park-number : ' . uniqid();
            $invoice->total_price = $item['price'] * $quantity;
            $invoice->created_at = now();
            $invoice->updated_at = now();
            $invoice->save();
            $savedInvoices[] = $invoice->id;
            
            // ====== طباعة عدد النسخ المحدد ======
            for ($i = 0; $i < $printCopies; $i++) {
                $receiptHtml = $this->makePartial('print_invoice', [
                    'item' => $item,
                    'copy_number' => $i + 1,
                    'uuid'=> $invoice->uuid,
                    'total_copies' => $printCopies,
                    'invoice_id' => $invoice->id,
                    'created_at' => now()->format('d/m/Y h:i A'),
                    'quantity' => 1,
                    'total_price' => $item['price'] * $quantity
                ]);
                
                $receiptsHtml[] = $receiptHtml;
            }
        }
        
        return [
            'success' => true,
            'message' => 'تم إنشاء الفواتير بنجاح',
            'invoice_ids' => $savedInvoices,
            'receipts' => $receiptsHtml
        ];
        
    } catch (\Exception $e) {
        return [
            'success' => false,
            'message' => 'حدث خطأ: ' . $e->getMessage()
        ];
    }
}

    // ====== دالة التقارير ======
    public function reports()
    {
        $this->pageTitle = trans('jaber.player::lang.plugin.reports');
        
        // تعيين التاريخ الافتراضي لليوم
        $this->vars['selected_date'] = date('Y-m-d');
        $this->vars['reports_data'] = [];
        $this->vars['summary'] = [];
        
        // جلب جميع المنتجات لعرضها في الفلتر
        $this->vars['products'] = Product::with('prices')->get();
    }

  public function onGetReports()
    {
        $date = post('date_value');
        
        if (!$date) {
            $date = date('Y-m-d');
        }
        
        // جلب جميع المنتجات
        $products = Product::orderBy('name')->get();
        
        // جلب جميع الفواتير في التاريخ المحدد
        $invoices = Invoice::whereDate('created_at', $date)->get();
        
        $reportData = [];
        $totalQuantity = 0;
        $totalRevenue = 0;
        
        // حساب مبيعات كل منتج
        foreach ($products as $product) {
            // جلب فواتير هذا المنتج في التاريخ المحدد
            $productInvoices = $invoices->where('product_id', $product->id);
            
            // حساب الكمية والإيرادات
            $quantity = $productInvoices->sum('number');
            $revenue = $productInvoices->sum('total_price');
            
            // إذا كان هناك مبيعات للمنتج
            if ($quantity > 0 || $revenue > 0) {
                $totalQuantity += $quantity;
                $totalRevenue += $revenue;
                
                $reportData[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image' => $product->image,
                    'quantity' => $quantity,
                    'revenue' => $revenue
                ];
            }
        }
        
        // ترتيب حسب الأكثر مبيعاً
        usort($reportData, function($a, $b) {
            return $b['quantity'] - $a['quantity'];
        });
        
        // حساب إجمالي الفواتير
        $totalInvoices = $invoices->count();
        
        // تجهيز الملخص
        $summary = [
            'total_products' => count($reportData),
            'total_quantity' => $totalQuantity,
            'total_revenue' => $totalRevenue,
            'total_invoices' => $totalInvoices,
            'date' => $date
        ];
        
        return [
           
            'success' => true,
            'data' => $reportData,
            'summary' => $summary,
            '#body_new_new' => $this->makePartial('reports_table', [
                'reports_data' => $reportData,
                'summary' => $summary,
                'selected_date' => $date
            ])
        ];
    }


    

}

<?php

namespace Jaber\Player\Controllers;

use Jaber\Player\Models\Invoice;
use Backend\Classes\Controller;
use BackendMenu;
use Jaber\Player\Models\Product;
use Flash;
use DB;

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
            $savedInvoices = [];
            
            // حفظ الفواتير في قاعدة البيانات
            foreach ($invoiceData['items'] as $item) {
                // لكل منتج، نكرر الحفظ حسب الكمية
                for ($i = 0; $i < $item['quantity']; $i++) {
                    $invoice = new Invoice();
                    $invoice->product_id = $item['product_id'];
                    $invoice->price_id = $item['price_id'];
                    $invoice->number = 1; // كل إيصال يمثل قطعة واحدة
                    $invoice->total_price = $item['price'];
                    $invoice->created_at = now();
                    $invoice->updated_at = now();
                    $invoice->save();
                    $savedInvoices[] = $invoice->id;
                }
            }
            
            // ====== الطباعة المباشرة في الخلفية ======
            $this->printReceiptsInBackground($invoiceData['items']);
            
            return [
                'success' => true,
                'message' => 'تم إنشاء الفواتير وطباعتها بنجاح',
                'invoice_ids' => $savedInvoices
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ];
        }
    }

     /**
     * طباعة الإيصالات في الخلفية (لكل منتج بعدد الوصلات حسب الكمية)
     */
    private function printReceiptsInBackground($items)
    {
        // اسم الطابعة كما يظهر في Windows (استخدم الأمر Get-Printer لمعرفته)
        $printerName = "Rongta RP850"; // غيّر هذا حسب اسم الطابعة عندك
        
        try {
            $connector = new WindowsPrintConnector($printerName);
            $printer = new Printer($connector);
            
            // لكل منتج في الفاتورة
            foreach ($items as $item) {
                $quantity = (int)$item['quantity'];
                
                // طباعة إيصال منفصل لكل قطعة
                for ($i = 0; $i < $quantity; $i++) {
                    $this->printSingleReceipt($printer, $item, $i + 1, $quantity);
                    
                    // قص الورق بعد كل إيصال (إذا كان آخر إيصال للمنتج، نضيف مسافة)
                    if ($i < $quantity - 1) {
                        $printer->feed(2);
                        $printer->cut();
                    } else {
                        $printer->feed(3);
                        $printer->cut();
                    }
                }
            }
            
            $printer->close();
            
        } catch (\Exception $e) {
            // تسجيل الخطأ دون إيقاف التنفيذ
            \Log::error('فشل الطباعة: ' . $e->getMessage());
        }
    }

    /**
     * طباعة إيصال واحد
     */
    private function printSingleReceipt($printer, $item, $copyNumber, $totalCopies)
    {
        $storeName = "حديقة أزادي";
        $storePhone = "0934240928";
        $designer = "جابر رسول";
        $accountant = "علي حاتم علي";
        $date = now()->format('d/m/Y h:i A');
        $invoiceId = date('Ymd') . rand(1000, 9999);
        
        // مركز النص
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text($storeName . "\n");
        $printer->text("==============================\n");
        
        // معلومات الإيصال
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("رقم الفاتورة: #" . $invoiceId . "\n");
        $printer->text("التاريخ: " . $date . "\n");
        
        // إذا كان هناك أكثر من نسخة
        if ($totalCopies > 1) {
            $printer->text("النسخة: " . $copyNumber . " من " . $totalCopies . "\n");
        }
        
        $printer->text("------------------------------\n");
        
        // رأس الجدول
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text(sprintf("%-20s %5s %8s\n", "المنتج", "عدد", "السعر"));
        $printer->text("------------------------------\n");
        
        // المنتج
        $name = mb_substr($item['product_name'], 0, 15, 'UTF-8');
        $printer->text(sprintf("%-20s %5s %8s\n", $name, 1, number_format($item['price'])));
        
        $printer->text("------------------------------\n");
        
        // المجموع
        $printer->setJustification(Printer::JUSTIFY_RIGHT);
        $printer->text(sprintf("%-20s %20s\n", "المجموع:", number_format($item['price']) . " ر.س"));
        
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("==============================\n");
        $printer->text("شكراً لزيارتكم حديقة أزادي\n");
        $printer->text("الاعداد المحاسبي: " . $accountant . "\n");
        $printer->text("تصميم وبرمجة: " . $designer . "\n");
        $printer->text("للتواصل: " . $storePhone . "\n");
        $printer->text("==============================\n");
    }
//  public function onPrintInvoice()
//     {
//         $invoiceData = post('invoice_data');
//         $invoiceData = json_decode($invoiceData, true);
        
//         if (!$invoiceData || !isset($invoiceData['items']) || empty($invoiceData['items'])) {
//             return [
//                 'success' => false, 
//                 'message' => 'بيانات الفاتورة غير صحيحة'
//             ];
//         }

//         try {
//             $savedInvoices = [];
            
//             foreach ($invoiceData['items'] as $item) {
//                 $invoice = new Invoice();
//                 $invoice->product_id = $item['product_id'];
//                 $invoice->price_id = $item['price_id'];
//                 $invoice->number = $item['quantity'];
//                 $invoice->total_price = $item['subtotal'];
//                 $invoice->created_at = now();
//                 $invoice->updated_at = now();
//                 $invoice->save();
//                 $savedInvoices[] = $invoice->id;
//             }
            
//             $html = $this->makePartial('print_invoice', [
//                 'items' => $invoiceData['items'],
//                 'total' => $invoiceData['total'],
//                 'invoice_ids' => implode(', ', $savedInvoices),
//                 'created_at' => now()->format('d/m/Y h:i A')
//             ]);
            
//             return [
//                 'success' => true,
//                 'message' => 'تم إنشاء الفواتير بنجاح',
//                 'invoice_ids' => $savedInvoices,
//                 'html' => $html
//             ];
            
//         } catch (\Exception $e) {
//             return [
//                 'success' => false,
//                 'message' => 'حدث خطأ: ' . $e->getMessage()
//             ];
//         }
//     }


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

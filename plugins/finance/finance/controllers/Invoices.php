<?php namespace Finance\Finance\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Finance\Finance\Models\Invoice;
use Finance\Finance\Models\Month;
use Finance\Finance\Models\Year;
use Flash;

class Invoices extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController' ,  \Backend\Behaviors\RelationController::class,    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'invoices' 
    ];

    public $relationConfig = 'relation_config.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Finance.Finance', 'finance_menu', 'invoices');
        $this->addCss('/plugins/finance/finance/assets/css/style_button.css', 'finance.finance');
    }

           public function formGetRedirectUrl($context = null, $model = null)
    {
               $url = post('url');
        if (($url == 'create') && !empty($url)) {
            return "finance/finance/invoices/create";
        }else if (($url == 'preview') && !empty($url)) {
            return "finance/finance/invoices/$url/$model->id";
        }else {
            if ((post("close") == 1) && !empty(post("close"))) {
                return "finance/finance/invoices";
            } else {
                return "finance/finance/invoices/update/$model->id";
            }
        }
    }

public function onDailyFundMovement()
{
    $invoices = \Finance\Finance\Models\Invoice::all();
    
    return [
        '#Lists' => $this->makePartial('daily_fund_movement', [
            'invoices' => $invoices
        ]),
        '#Filter-listFilter' => ' ',
    ];
}


public function reports(){
    $this->pageTitle = trans('finance.finance::lang.plugin.print_tables');
    $this->vars['yearsOptions'] = Year::get();
}



    public function onGetMonths()
    {
        $yearId = post('year_id');
        $months = Month::where('year_id', $yearId)->get();

        return [
            '#monthSelect' => $this->makePartial('monthoptions', ['months' => $months]),
        ];
    }
public function onFilterReports()
{
    $year_id = post('year_id');
    $month_id = post('month_id');
    $currency = post('currency');

    if(empty($year_id) || empty($month_id) || empty($currency)){
        Flash::error('تحديد السنة والشهر والعملة مطلوبين');
        return;
    }

    $query = Invoice::with(['model_type', 'month', 'year']);
    $invoices = $query->where('year_id', $year_id)
                      ->where('month_id', $month_id)
                      ->where('currency', $currency)
                      ->get();

    $this->vars['invoices'] = $invoices;

    // ====== حساب الإحصائيات ======
    $statistics = [
        'total_invoices' => $invoices->count(),
        'total_payments' => 0,
        'total_receipts' => 0,
        'balance' => 0,
        'model_types' => []
    ];

    // تجميع الإحصائيات حسب model_type
    $groupedByModelType = [];

    foreach($invoices as $invoice) {
        $typeName = $invoice->model_type->name ?? 'غير محدد';
        $typeId = $invoice->model_type->id ?? 0;
        $type = $invoice->type; // 'payment' or 'receipt'
        $amount = (float) $invoice->amount;

        // تجميع المبالغ حسب النوع العام
        if ($type == 'payment') {
            $statistics['total_payments'] += $amount;
        } else if ($type == 'receipt') {
            $statistics['total_receipts'] += $amount;
        }

        // تجميع المبالغ حسب model_type
        $key = $typeId . '_' . $typeName;
        if (!isset($groupedByModelType[$key])) {
            $groupedByModelType[$key] = [
                'id' => $typeId,
                'name' => $typeName,
                'type' => $type,
                'count' => 0,
                'total' => 0
            ];
        }
        $groupedByModelType[$key]['count']++;
        $groupedByModelType[$key]['total'] += $amount;
    }

    // ترتيب النتائج
    $statistics['model_types'] = array_values($groupedByModelType);
    
    // ترتيب حسب النوع (الدفع أولاً ثم القبض)
    usort($statistics['model_types'], function($a, $b) {
        if ($a['type'] == $b['type']) {
            return $a['name'] <=> $b['name'];
        }
        return $a['type'] == 'payment' ? -1 : 1;
    });

    // حساب الرصيد = المقبوضات - المدفوعات
    $statistics['balance'] = $statistics['total_receipts'] - $statistics['total_payments'];

    $this->vars['statistics'] = $statistics;
    // ====== نهاية حساب الإحصائيات ======

    return [
        '#body_table' => $this->makePartial('table', ['invoices' => $invoices , 'currency' => $currency == 'dollar' ? '$' : 'ل.س']),
        '#statistics-container' => $this->makePartial('statistics', ['statistics' => $statistics , 'currency' => $currency == 'dollar' ? '$' : 'ل.س']),
    ];
}


}

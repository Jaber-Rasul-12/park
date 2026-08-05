<?php namespace Finance\Finance\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

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
            return "finance/finance/invoices";
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
}

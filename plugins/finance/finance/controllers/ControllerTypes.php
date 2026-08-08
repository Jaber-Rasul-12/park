<?php namespace Finance\Finance\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class ControllerTypes extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'types' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Finance.Finance', 'finance_menu', 'types');
    }


                   public function formGetRedirectUrl($context = null, $model = null)
    {
               $url = post('url');
        if (($url == 'create') && !empty($url)) {
            return "finance/finance/controllertypes/create";
        }else if (($url == 'preview') && !empty($url)) {
            return "finance/finance/controllertypes/$url/$model->id";
        }else {
            if ((post("close") == 1) && !empty(post("close"))) {
                return "finance/finance/controllertypes";
            } else {
                return "finance/finance/controllertypes/update/$model->id";
            }
        }
    }
}

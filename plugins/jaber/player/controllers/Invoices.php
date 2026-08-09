<?php

namespace Jaber\Player\Controllers;

use Jaber\Player\Models\Invoice;
use Backend\Classes\Controller;
use BackendMenu;

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


    

}

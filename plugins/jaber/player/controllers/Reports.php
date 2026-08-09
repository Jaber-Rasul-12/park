<?php

namespace Jaber\Player\Controllers;

use Jaber\Player\Models\Invoice;
use Backend\Classes\Controller;
use BackendMenu;
use Flash;
use Winter\Storm\Support\Facades\Validator;
use ValidationException;

class Reports extends Controller
{

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Jaber.Player', 'player', 'reports');
        $this->pageTitle = trans('jaber.player::lang.plugin.reports');
    }

    public function index()
    {
        $this->addCss('/plugins/jaber/player/assets/filesignatore/jquery.signaturepad.css', 'Jaber.Finance');
        $this->addJs('/plugins/jaber/player/assets/filesignatore/jquery.signaturepad.js', 'Jaber.Finance');
        $this->addJs('/plugins/jaber/player/assets/filesignatore/json2.min.js', 'Jaber.Finance');
    }






    public function onReports()
    {


        $from_date = post('from_date');
        $to_date = post('to_date');
        $rules = [
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ];


        $validator = Validator::make(post(), $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        } else {

            $invoice_items  = Invoice::whereBetween('created_at', [$from_date, $to_date])->get();
        }
        return [
            '.form-preview' => $this->makePartial('preview', [
                'invoice_items' => $invoice_items,
                'from_date' => $from_date,
                'to_date' => $to_date,
            ]),
        ];
    }
}

<?php namespace Finance\Finance\Models;

use Finance\Finance\Classes\NumberToWords;
use Model;
// use Winter\Storm\Database\Builder;
// use BackendAuth;
/**
 * Model
 */

use Jacob\Logbook\Traits\LogChanges;
use Ramsey\Uuid\Guid\Guid;
class Invoice extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
        use LogChanges;
  public $logBookModelName = 'finance.finance::lang.plugin.invoices';
  public static function changeLogBookDisplayColumn($column)
  {
    return 'finance.finance::lang.model.invoice.' . $column;
  }
   
    /**
     * @var string The database table used by the model.
     */
    public $table = 'finance_finance_invoices';

      public $rules = [
        'center_id'         => 'required|exists:finance_finance_centers,id',
        'type'                  => 'required|string|max:255',
        'amount_name'                  => 'required|string|max:255',
        'payment_from'          => 'required|string|max:255',
        'payment_to'            => 'required|string|max:255',
        'currency'              => 'required|string|max:255',
        'amount'                => 'required|numeric|min:0',
        'disbursement_statement' => 'required|string|max:255',
        'uuid'                  => 'required|string|uuid',
        'year_id'         => 'required|exists:finance_finance_years,id',
        'month_id'         => 'required|exists:finance_finance_months,id',
    ];


        public $belongsTo = [
        'model_type' => ['Finance\Finance\Models\ModelType', 'key' => 'type_id'],
        'center' => ['Finance\Finance\Models\Center', 'key' => 'center_id'],    
        'month'        => ['Finance\Finance\Models\Month', 'key' => 'month_id'],
        'year'        => ['Finance\Finance\Models\Year', 'key' => 'year_id'],
    ];


        public $attachMany = [
        'photos' => 'System\Models\File'
    ];



      /**
   * Generates a UUID before creating the model record.
   */
  public function beforeValidate()
  {
    if (empty($this->uuid)) {
      $this->uuid = Guid::uuid4()->toString();
    }
  }

        public function filterFields($fields, $context = null)
  {         
      if (isset($fields->amount->value) && !empty($fields->amount->value) && isset($fields->currency->value) && !empty($fields->currency->value)) {
                $fields->amount_name->value =  NumberToWords::convert($fields->amount->value);   
                $fields->amount_name->value = $fields->amount_name->value . ' ' .  ($fields->currency->value == 'dollar' ? 'دولار فقط لا غير' : 'ليرة سورية فقط لا غير ');     

      }else{
            $fields->amount_name->value = '';

          }


  }



  public function getTypeOptions()
  {

  return ['payment'=>trans('finance.finance::lang.model.invoice.payment') , 'receipt' => trans('finance.finance::lang.model.invoice.receipt')];
    
  }
  public function getCurrencyOptions()
  {
      return ['dollar'=>trans('finance.finance::lang.model.invoice.dollar') , 'syrian' => trans('finance.finance::lang.model.invoice.syrian')];
    
  }


    public function getTypeIdOptions()
  {
    if (!empty($this->type) && isset($this->type)) {
      return ModelType::where('type', $this->type)->get()->lists('name', 'id');
    } else {
      return [];
    }
  }

   public function getTypeListsAttribute()
  {
    return trans('finance.finance::lang.model.invoice.' . $this->attributes['type']);
  }

   public function getCurrencyListsAttribute()
  {
    return trans('finance.finance::lang.model.invoice.' . $this->attributes['currency']);
  }

  

    public function getMonthOptions($scopes = null)
  {
    if (!empty($scopes['year']->value)) {
      return Month::whereIn('year_id', array_keys($scopes['year']->value))->get()->lists('name', 'id');
    } else {
      return [];
    }
  }

         public function getYearIdOptions()
  {
      return Year::where('status' , true)->get()->lists('name', 'id');
  }

       public function getMonthIdOptions()
  {
    if (isset($this->year) && !empty($this->year->id)) {
      return Month::where('year_id', $this->year->id)->where('status' , true)->get()->lists('name', 'id');
    } else {
      return [];
    }
  }


  public function getModelTypeOptions($scopes = null)
{
    if (!empty($scopes['type']->value)) {
        return ModelType::whereIn('type', array_keys($scopes['type']->value))->lists('name', 'id');
    } else {
        return ModelType::lists('name', 'id');
    }
}
  


}

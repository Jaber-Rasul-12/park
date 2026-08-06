<?php

namespace Finance\Finance\Models;


use Model;
// use Winter\Storm\Database\Builder;
use BackendAuth;


/**
 * Model
 */
class Month extends Model
{
  use \Winter\Storm\Database\Traits\Validation;




  /**
   * @var string The database table used by the model.
   */
  public $table = 'finance_finance_months';

  public $rules = [
    'name' => 'required|in:1,2,3,4,5,6,7,8,9,10,11,12',
    'year_id' => 'required|exists:finance_finance_years,id',
    'status'      => 'required|boolean',
    'user_id' => 'nullable|exists:backend_users,id',
  ];

  public $belongsTo = [
    'user' => ['Backend\Models\User', 'key' => 'user_id'],

    'year' => ['Finance\Finance\Models\Year', 'scope' => 'isActive']
  ];


          public $hasMany = [
        'invoice' => [Invoice::class, 'key' => 'month_id'],
    ];





  /**
   * Perform actions before deleting 
   *
   * @throws \ValidationException
   */
  public function beforeDelete()
  {

    // Check if there are associated records in any of the hasMany relationships
    foreach ($this->hasMany as $relation => $details) {
      if ($this->{$relation}->count() > 0) {
        // If associated records exist, prevent deletion and throw a validation exception
        throw new \ValidationException(['name' => trans('finance.finance::lang.plugin.message_delete')]);
      }
    }
  }
  // before the model is saved, when first created.
  public function beforeCreate()
  {
    $this->checkUniqueNameYear();
    if ($this->status) {
      $this->statusMonth();
    }
  }

public function afterCreate()
{
   
}


  public function beforeUpdate()
  {
    if (($this->original['year_id'] != $this->year_id) || ($this->original['name'] != $this->name)) {
      $this->checkUniqueNameYear();
    }
    if (($this->original['status'] == false) && ($this->status == true)) {
      $this->statusMonth();
     
    }
  }

  protected function checkUniqueNameYear()
  {
    $exists = self::where('year_id', $this->year_id)
      ->where('name', $this->name)
      ->exists();

    if ($exists) {
      throw new \ValidationException(['name' => trans('finance.finance::lang.plugin.message_unique')]);
    }
  }

  protected function statusMonth()
  {
    $exists = self::where('status', true)
      ->exists();

    if ($exists) {
      throw new \ValidationException(['status' => trans('finance.finance::lang.plugin.error_status_save')]);
    }
  }

  public function beforeSave()
  {
    $this->user_id = isset(BackendAuth::getUser()->id) ? BackendAuth::getUser()->id : null;
  }
}

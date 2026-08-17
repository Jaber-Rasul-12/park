<?php

namespace Jaber\Player\Models;

use Model;
// use Winter\Storm\Database\Builder;
// use BackendAuth;
/**
 * Model
 */
use Jacob\Logbook\Traits\LogChanges;
class Price extends Model
{
  use \Winter\Storm\Database\Traits\Validation;

        use LogChanges;
  public $logBookModelName = 'jaber.player::lang.plugin.prices';
  public static function changeLogBookDisplayColumn($column)
  {
    return 'jaber.player::lang.model.price.' . $column;
  }

  /**
   * @var string The database table used by the model.
   */
  public $table = 'jaber_player_prices';

  public $fillable = ['price'];

  public $rules = [
    'price' => 'required|numeric',
    'product_id'  => 'required|exists:jaber_player_products,id',
  ];

  /**
   * Relationships
   */
  public $belongsTo = [
    
    'product' => [
      \Jaber\Player\Models\Product::class,
      'key' => 'product_id',
    ],
  ];
  public $hasMany = [
    'invoices' => ['Jaber\Player\Models\Invoice', 'key' => 'price_id'],
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
        throw new \ValidationException(['name' => trans('jaber.player::lang.plugin.message_delete')]);
      }
    }
  }

   public function beforeCreate()
  {
    if ($this->status) {
      $this->statusPirce();
    }
  }




  public function beforeUpdate()
  {
    if (($this->original['status'] == false) && ($this->status == true) && ($this->original['product_id'] == false) && ($this->product_id == true)) {
      $this->statusPirce();
     
    }
  }
  protected function statusPirce()
  {
    $exists = self::where('status', true)->where('product_id', $this->product_id)
      ->exists();

    if ($exists) {
      throw new \ValidationException(['status' => trans('jaber.player::lang.plugin.error_status_save')]);
    }
  }
}

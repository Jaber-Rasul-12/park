<?php

namespace Jaber\Player\Models;

use Model;
// use Winter\Storm\Database\Builder;
// use BackendAuth;
/**
 * Model
 */
class Product extends Model
{
  use \Winter\Storm\Database\Traits\Validation;


 use \Winter\Storm\Database\Traits\Purgeable; 
 protected $purgeable = ['price'];

  /**
   * @var string The database table used by the model.
   */
  public $table = 'jaber_player_products';

  /**
   * Validation rules
   */
  public $rules = [
    'name' => 'required|unique:jaber_player_products,name',
  ];

   /**
   * Defines a "hasMany" relationship.
   *
   * - Establishes a one-to-many relationship between this model and the `nameClass` model.
   * - The foreign key `key_relation_id` is used to link multiple related records.
   * - This allows retrieving multiple `nameRelation` records associated with this model.
   *
   * @var array
   */
  public $hasMany = [
    'prices' => ['Jaber\Player\Models\Price', 'key' => 'product_id'],
    'invoices' => ['Jaber\Player\Models\Invoice', 'key' => 'product_id'],

    
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
        if(empty($this->getOriginalPurgeValue('price'))){
            throw new \ValidationException(['price' => trans('jaber.player::lang.plugin.price_require')]);;
        }
    }

    public function afterCreate()
    {
        $this->prices()->create([
            'price' => $this->getOriginalPurgeValue('price'),
            'status' => 1
        ]);
    }
}

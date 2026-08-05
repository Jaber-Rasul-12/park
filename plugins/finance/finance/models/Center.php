<?php namespace Finance\Finance\Models;

use Model;
use Finance\Finance\Models\Invoice;
use Jacob\Logbook\Traits\LogChanges;
class Center extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    
    use LogChanges;
  public $logBookModelName = 'finance.finance::lang.plugin.centers';
  public static function changeLogBookDisplayColumn($column)
  {
    return 'finance.finance::lang.model.center.' . $column;
  }

    /**
     * @var string The database table used by the model.
     */
    public $table = 'finance_finance_centers';

    /**
     * @var array Validation rules
     */
       /**
     * @var array Validation rules for the model attributes.
     */
    public $rules = [
        'name' => 'required|string|max:255|unique:finance_finance_centers,name',
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
        'invoice' => [Invoice::class, 'key' => 'center_id'],
    ];

             /**
     * Perform actions before deleting 
     *
     * @throws \ValidationException
     */
    public function beforeDelete()
    {
        foreach ($this->hasMany as $relation => $details) {
            if ($this->{$relation}->count() > 0) {
                throw new \ValidationException(['name' => trans('finance.finance::lang.plugin.message_delete')]);
            }
        }
    }


}

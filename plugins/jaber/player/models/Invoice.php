<?php namespace Jaber\Player\Models;

use Model;
// use Winter\Storm\Database\Builder;
// use BackendAuth;
/**
 * Model
 */
class Invoice extends Model
{
    use \Winter\Storm\Database\Traits\Validation;



    /**
     * @var string The database table used by the model.
     */
    public $table = 'jaber_player_invoices';
    public $fillable = ['product_id' , 'price_id' , 'number' ,'total_price'];

  public $rules = [
        'product_id' => 'required|exists:jaber_player_products,id',
        'price_id' => 'required|exists:jaber_player_prices,id',
        'number' => 'required|numeric|min:1',
    ];



    public $belongsTo = [
        'product' => [
            'Jaber\Player\Models\Product',
            'key' => 'product_id'
        ],
        'price' => [
            'Jaber\Player\Models\Price',
            'key' => 'price_id'
        ],
    ];

   
  public function getPriceIdOptions()
  {
    if ( isset($this->product->id) && !empty($this->product->id)) {
      return Price::where('product_id', $this->product->id)->get()->lists('price', 'id');
    } else {
      return [];
    }
  }

    /**
   * Get dropdown options for a specified field.
   *
   * - This method is used to provide dropdown options dynamically.
   * - It accepts the field name, current value, and form data as parameters.
   * - Returns an array of options that can be used in dropdown selections.
   *
   * @param  string  $fieldName The name of the field for which options are retrieved.
   * @param  mixed   $value The current value of the field.
   * @param  array   $formData The form data available for context.
   * @return array   An array of dropdown options.
   */
  public function getDropdownOptions($fieldName, $value, $formData)
  {
    return [];
  }

    /**
   * Filter and set options for form fields based on certain conditions.
   *
   * @param object $fields   The form fields.
   * @param mixed  $context  Additional context information if needed.
   *
   */
  public function filterFields($fields, $context = null)
  {

    if ((isset($fields->product) && !empty($fields->product->value)) && (isset($fields->price_id) && !empty($fields->price_id->value)) && (isset($fields->number) && !empty($fields->number->value))) {
      $fields->total_price->value = Price::where('id', $fields->price_id->value)->first()->price * $fields->number->value;
    } else {
      $fields->total_price->value = 0;
    }
  }

}

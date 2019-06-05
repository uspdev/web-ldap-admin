<?php
namespace App\Rules;
use Illuminate\Contracts\Validation\Rule;
use Respect\Validation\Validator as v;
class Numeros_USP implements Rule
{
    private $field;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($field = null)
    {
        $this->field = $field;
    }
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if(empty(trim($value))){
            return true;
        }
        $values = explode(',',$value);
        foreach($values as $v) {
            if (!(is_numeric(trim($v)))) {
                return false;
            }
        }
        return true;
    }
    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->field . ': Número USP precisa ser númerico';
    }
}

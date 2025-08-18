<?php
namespace Cums\Validator;

use Fuel\Core\Input;
use Fuel\Core\Log;
use Fuel\Core\Validation;

abstract class Validator
{
    protected $parameters;
    protected $validation;

    public function __construct($parameters, $fieldset = false)
    {
        $this->parameters = is_string($parameters) ? Input::{$parameters}() : $parameters;
        $this->validation = ($fieldset ? Validation::forge($fieldset) : Validation::forge());
    }

    public function validate()
    {
        if ( ! $this->validation->run($this->parameters))
        {
            foreach ($this->validation->error() as $field => $error)
            {
                Log::info('$field:' . var_export($field, true));
                Log::info('$error->get_message():' . var_export($error->get_message(), true));
                throw new \Exception($error->get_message(), 1);
            }
        }
    }
}

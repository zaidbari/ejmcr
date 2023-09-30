<?php

namespace App\Traits;

use Rakit\Validation\Validator;

trait Validation
{

    use Request;

    /**
     * @param array $rules
     */

    protected array $validation_errors = [];

    /**
     * @param array $rules rules to validate against
     * 
     * @return bool
     */
    protected function validate( array $rules = [] ) : bool
    {
        $validator = new Validator;

        $validation = $validator->validate($this->requestBody() + $_FILES, $rules);

        if ($validation->fails() ) {
            $this->validation_errors = $validation->errors()->firstOfAll();
            return false;
        }

        return true;
    }
    protected function validateJSON( array $rules = [] ) : bool
    {
        $validator = new Validator;

        $validation = $validator->validate((array) json_decode(file_get_contents('php://input')) + $_FILES, $rules);

        if ($validation->fails() ) {
            $this->validation_errors = $validation->errors()->firstOfAll();
            return false;
        }

        return true;
    }

}

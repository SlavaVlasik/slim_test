<?php

namespace App;

class Validator implements ValidatorInterface
{
    public function validate(array $user)
    {
        // BEGIN (write your solution here)
        
            $errors = [];
            if(empty($user['name'])){
                $errors['name'] = "Can't be blank";
            };
            if(empty($user['password'])){
                $errors['password'] = "Can't be blank";
            };
            return $errors;
      
        // END
    }
}
<?php

namespace tdt4237\webapp\validation;

use tdt4237\webapp\models\User;

class RegistrationFormValidation
{
    const MIN_USER_LENGTH = 3;
    
    private $validationErrors = [];
    
    public function __construct($username, $email, $password, $confirm_password, $first_name, $last_name, $phone, $company)
    {
        return $this->validate($username, $email, $password, $confirm_password, $first_name, $last_name, $phone, $company);
    }
    
    public function isGoodToGo()
    {
        return empty($this->validationErrors);
    }
    
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }

    private function validate($username, $email,  $password, $confirm_password, $first_name, $last_name, $phone, $company)
    {
        if (empty($password)) {
            $this->validationErrors[] = 'Password cannot be empty';
        }

        if($password != $confirm_password){
            $this->validationErrors[] = 'Passwords does not match';
        }

        if (strlen($password) < 8){
            $this->validationErrors[] = 'Password should be at least eight characters long';
        }
        if(!empty($password)){
            if (strpos($password, $first_name) !== false or strpos($password, $last_name) !== false or strpos($password, $username) !== false) {
                $this->validationErrors[] = 'Your password cannot contain you name or username';
            }
        }
        if(!preg_match('/[A-Z]/', $password) or (!preg_match('/[a-z]/', $password) or (preg_match('/\\d/', $password) < 0)) ){
            $this->validationErrors[] = 'Your passwords needs to consist of upper case letters, lower case letters and numbers';
        }
        if(empty($first_name)) {
            $this->validationErrors[] = "Please write in your first name";
        }

         if(empty($last_name)) {
            $this->validationErrors[] = "Please write in your last name";
        }

        if(empty($phone)) {
            $this->validationErrors[] = "Please write in your post code";
        }

        if (strlen($phone) != "8") {
            $this->validationErrors[] = "Phone number must be exactly eight digits";
        }

        if(strlen($company) > 0 && (!preg_match('/[^0-9]/',$company)))
        {
            $this->validationErrors[] = 'Company can only contain letters';
        }

        if (preg_match('/^[A-Za-z0-9_]+$/', $username) === 0) {
            $this->validationErrors[] = 'Username can only contain letters and numbers';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->validationErrors[] = 'Invalid email';
        }
    }
}

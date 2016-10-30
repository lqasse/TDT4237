<?php

namespace tdt4237\webapp;

class Hash
{


    public function __construct()
    {
    }

    public static function make($plaintext)
    {
        return password_hash($plaintext, PASSWORD_DEFAULT);
    }

    public function check($plaintext, $hash)
    {
        return password_verify($plaintext, $hash);
    }

}

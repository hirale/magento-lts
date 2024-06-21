<?php

class Mage_Oauth2_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function generateClientSecret($length = 40)
    {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }

    public function generateToken($clientId, $length = 40)
    {
        return bin2hex(openssl_random_pseudo_bytes($length)) . $clientId;
    }
}

<?php

class Mage_Oauth2_Model_AccessToken extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('oauth2/accessToken');
    }

    public function getUserType()
    {
        if ($this->getAdminId()) {
            return 'admin';
        } elseif ($this->getCustomerId()) {
            return 'customer';
        } else {
            Mage::throwException('User type is unknown');
        }
    }
}

<?php

class Mage_Oauth2_Block_Device_Verify extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        $this->setTemplate('oauth2/device/verify.phtml');
    }
    public function getFormActionUrl()
    {
        return $this->getUrl('oauth2/device/authorize');
    }

    public function getUserCode()
    {
        return Mage::registry('current_device_code');
    }
}

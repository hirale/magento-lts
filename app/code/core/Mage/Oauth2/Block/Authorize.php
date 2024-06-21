<?php
class Mage_Oauth2_Block_Authorize extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        $this->setTemplate('oauth2/authorize.phtml');
    }

    public function getClient()
    {
        $clientId = Mage::app()->getRequest()->getParam('client_id');
        return Mage::getModel('oauth2/client')->load($clientId, 'entity_id');
    }

    public function getScope()
    {
        return Mage::app()->getRequest()->getParam('scope');
    }

    public function getState()
    {
        return Mage::app()->getRequest()->getParam('state');
    }

    public function getRedirectUri()
    {
        return Mage::app()->getRequest()->getParam('redirect_uri');
    }

    public function getFormActionUrl()
    {
        return $this->getUrl('*/*/index', array('_secure' => true));
    }
}

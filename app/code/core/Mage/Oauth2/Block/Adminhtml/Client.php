<?php

class Mage_Oauth2_Block_Adminhtml_Client extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'oauth2';
        $this->_controller = 'adminhtml_client';
        $this->_headerText = Mage::helper('oauth2')->__('Manage OAuth2 Clients');
        $this->_addButtonLabel = Mage::helper('oauth2')->__('Add New Client');
        parent::__construct();
    }
}

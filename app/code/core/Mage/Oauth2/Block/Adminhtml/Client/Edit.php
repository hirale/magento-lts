<?php

class Mage_Oauth2_Block_Adminhtml_Client_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_model;
    public function getModel()
    {
        if ($this->_model === null) {
            $this->_model = Mage::registry('current_oauth2_client');
        }
        return $this->_model;
    }
    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'oauth2';
        $this->_controller = 'adminhtml_client';
        $this->_mode = 'edit';

        $this->_addButton('save_and_continue', [
            'label'     => Mage::helper('oauth')->__('Save and Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class' => 'save'
        ], 100);

        $this->_formScripts[] = "function saveAndContinueEdit()" .
            "{editForm.submit($('edit_form').action + 'back/edit/')}";

        $this->_updateButton('save', 'label', $this->__('Save'));
        $this->_updateButton('save', 'id', 'save_button');
        $this->_updateButton('delete', 'label', $this->__('Delete'));
        $this->_updateButton('delete', 'onclick', 'if(confirm(\'' . Mage::helper('core')->jsQuoteEscape(
            Mage::helper('adminhtml')->__('Are you sure you want to do this?')
        ) . '\')) editForm.submit(\'' . $this->getUrl('*/*/delete') . '\'); return false;');

        /** @var Mage_Admin_Model_Session $session */
        $session = Mage::getSingleton('admin/session');
        if (!$this->getModel() || !$this->getModel()->getId() || !$session->isAllowed('system/oauth2/client/delete')) {
            $this->_removeButton('delete');
        }
    }

    public function getHeaderText()
    {
        $model = $this->getModel();
        if ($model->getId()) {
            return $this->__("Edit Client '%s'", $this->escapeHtml($model->getName()));
        }
        return $this->__('New Client');
    }
}

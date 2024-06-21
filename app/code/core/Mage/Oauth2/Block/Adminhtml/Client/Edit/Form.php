<?php

class Mage_Oauth2_Block_Adminhtml_Client_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected $_model;

    public function getModel()
    {
        if ($this->_model === null) {
            $this->_model = Mage::registry('current_oauth2_client');
        }
        return $this->_model;
    }
    protected function _prepareForm()
    {
        $model = $this->getModel();
        $form = new Varien_Data_Form(
            array(
                'id' => 'edit_form',
                'action' => $this->getData('action'),
                'method' => 'post'
            )
        );

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => Mage::helper('oauth2')->__('Client Information'), 'class' => 'fieldset-wide'));
        $fieldset->addType('text', Mage::getConfig()->getBlockClassName('oauth2/adminhtml_text'));
        $fieldset->addField(
            'name',
            'text',
            array(
                'label' => Mage::helper('oauth2')->__('Client Name'),
                'name' => 'name',
                'required' => true,
                'value' => $model->getName(),
            )
        );
        $fieldset->addField(
            'secret',
            'text',
            array(
                'label' => Mage::helper('oauth2')->__('Client Secret'),
                'name' => 'secret',
                'required' => true,
                'disabled' => true,
                'data-copy-text' => $model->getSecret(),
                'value' => $model->getSecret(),
            )
        );

        $fieldset->addField(
            'redirect_uri',
            'text',
            array(
                'label' => Mage::helper('oauth2')->__('Redirect URI'),
                'name' => 'redirect_uri',
                'required' => true,
                'value' => $model->getRedirectUri(),
            )
        );
        $fieldset->addField(
            'grant_types',
            'multiselect',
            array(
                'label' => Mage::helper('oauth2')->__('Grant Types'),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'grant_types[]',
                'values' => array(
                    array('value' => 'authorization_code', 'label' => Mage::helper('oauth2')->__('Authorization Code')),
                    array('value' => 'client_credentials', 'label' => Mage::helper('oauth2')->__('Client Credentials')),
                    array('value' => 'refresh_token', 'label' => Mage::helper('oauth2')->__('Refresh Token')),
                ),
                'value' => $model->getGrantTypes(),
            )
        );

        $fieldset->addField(
            'scope',
            'multiselect',
            array(
                'label' => Mage::helper('oauth2')->__('Scope'),
                'name' => 'scope[]',
                'required' => true,
                'values' => [
                    ['value' => 'read', 'label' => Mage::helper('oauth2')->__('Read')],
                    ['value' => 'write', 'label' => Mage::helper('oauth2')->__('Write')],
                ],
                'value' => $model->getScope(),
            )
        );

        $fieldset->addField(
            'current_password',
            'obscure',
            [
                'name' => 'current_password',
                'label' => Mage::helper('oauth')->__('Current Admin Password'),
                'required' => true
            ]
        );

        $form->setAction($this->getUrl('*/*/save'));
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}

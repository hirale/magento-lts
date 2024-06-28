<?php

class Mage_Oauth2_Block_Adminhtml_Client_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_editAllow = false;
    public function __construct()
    {
        parent::__construct();
        $this->setId('oauth2_client_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $session = Mage::getSingleton('admin/session');
        $this->_editAllow = $session->isAllowed('system/oauth/consumer/edit');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('oauth2/client')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => Mage::helper('oauth2')->__('Entity ID'),
                'index' => 'entity_id',
                'type' => 'number',
            ]
        );

        $this->addColumn(
            'secret',
            [
                'header' => Mage::helper('oauth2')->__('Secret'),
                'index' => 'secret',
            ]
        );

        $this->addColumn(
            'redirect_uri',
            [
                'header' => Mage::helper('oauth2')->__('Redirect URI'),
                'index' => 'redirect_uri',
            ]
        );

        $this->addColumn(
            'scope',
            [
                'header' => Mage::helper('oauth2')->__('Scope'),
                'index' => 'scope',
            ]
        );

        $this->addColumn(
            'grant_types',
            [
                'header' => Mage::helper('oauth2')->__('Grant Types'),
                'index' => 'grant_types',
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => Mage::helper('oauth2')->__('Created At'),
                'index' => 'created_at',
                'type' => 'datetime',
            ]
        );

        $this->addColumn(
            'updated_at',
            [
                'header' => Mage::helper('oauth2')->__('Updated At'),
                'index' => 'updated_at',
                'type' => 'datetime',
            ]
        );

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }
    public function getRowUrl($row)
    {
        if ($this->_editAllow) {
            return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
        }
        return null;
    }
}

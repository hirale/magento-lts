<?php

/**
 * OpenMage
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available at https://opensource.org/license/osl-3-0-php
 *
 * @category   Mage
 * @package    Mage_CatalogSearch
 * @copyright  Copyright (c) 2006-2020 Magento, Inc. (https://www.magento.com)
 * @copyright  Copyright (c) 2019-2025 The OpenMage Contributors (https://www.openmage.org)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Fulltext Collection
 *
 * @category   Mage
 * @package    Mage_CatalogSearch
 */
class Mage_CatalogSearch_Model_Resource_Fulltext_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    /**
     * Name for relevance order
     */
    public const RELEVANCE_ORDER_NAME = 'relevance';

    /**
     * Found data
     *
     * @var array|null
     */
    protected $_foundData = null;

    /**
     * Sort order by relevance
     *
     * @var int
     */
    protected $_relevanceSortOrder = SORT_DESC;

    /**
     * Sort by relevance flag
     *
     * @var bool
     */
    protected $_sortByRelevance = false;

    /**
     * Is search filter applied flag
     *
     * @var bool
     */
    protected $_isSearchFiltersApplied = false;

    /**
     * Retrieve query model object
     *
     * @return Mage_CatalogSearch_Model_Query
     */
    protected function _getQuery()
    {
        return Mage::helper('catalogsearch')->getQuery();
    }

    /**
     * Add search query filter
     *
     * @param string $query
     * @return $this
     */
    public function addSearchFilter($query)
    {
        return $this;
    }

    /**
     * Before load handler
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _beforeLoad()
    {
        if (!$this->_isSearchFiltersApplied) {
            $this->_applySearchFilters();
        }

        return parent::_beforeLoad();
    }

    /**
     * Get collection size
     *
     * @return int
     */
    public function getSize()
    {
        if (!$this->_isSearchFiltersApplied) {
            $this->_applySearchFilters();
        }

        return parent::getSize();
    }

    /**
     * Apply collection search filter
     *
     * @return $this
     */
    protected function _applySearchFilters()
    {
        $foundIds = $this->getFoundIds();
        if (!empty($foundIds)) {
            $this->addIdFilter($foundIds);
        } else {
            $this->getSelect()->where('FALSE');
        }
        $this->_isSearchFiltersApplied = true;

        return $this;
    }

    /**
     * Get found products ids
     *
     * @return array
     */
    public function getFoundIds()
    {
        if (is_null($this->_foundData)) {
            /** @var Mage_CatalogSearch_Model_Fulltext $preparedResult */
            $preparedResult = Mage::getSingleton('catalogsearch/fulltext');
            $preparedResult->prepareResult();
            $this->_foundData = $preparedResult->getResource()->getFoundData();
        }
        if (isset($this->_orders[self::RELEVANCE_ORDER_NAME])) {
            $this->_resortFoundDataByRelevance();
        }
        return array_keys($this->_foundData);
    }

    /**
     * Resort found data by relevance
     *
     * @return $this
     */
    protected function _resortFoundDataByRelevance()
    {
        if (is_array($this->_foundData)) {
            $data = [];
            foreach ($this->_foundData as $id => $relevance) {
                $this->_foundData[$id] = $relevance . '_' . $id;
            }
            natsort($this->_foundData);
            if ($this->_relevanceSortOrder == SORT_DESC) {
                $this->_foundData = array_reverse($this->_foundData);
            }
            foreach ($this->_foundData as $dataString) {
                [$relevance, $id] = explode('_', $dataString);
                $data[$id] = $relevance;
            }
            $this->_foundData = $data;
        }
        return $this;
    }

    /**
     * Set Order field
     *
     * @param string $attribute
     * @param string $dir
     * @return $this
     */
    public function setOrder($attribute, $dir = 'desc')
    {
        if ($attribute == 'relevance') {
            $this->_relevanceSortOrder = ($dir == 'asc') ? SORT_ASC : SORT_DESC;
            $this->addOrder(self::RELEVANCE_ORDER_NAME);
        } else {
            parent::setOrder($attribute, $dir);
        }
        return $this;
    }

    /**
     * Add sorting by relevance to select
     *
     * @return $this
     */
    protected function _addRelevanceSorting()
    {
        $foundIds = $this->getFoundIds();
        if (!$foundIds) {
            return $this;
        }

        /** @var Mage_CatalogSearch_Model_Resource_Helper_Mysql4 $resourceHelper */
        $resourceHelper = Mage::getResourceHelper('catalogsearch');
        $this->_select->order(
            new Zend_Db_Expr(
                $resourceHelper->getFieldOrderExpression(
                    'e.' . $this->getResource()->getIdFieldName(),
                    $foundIds,
                )
                . ' ' . Zend_Db_Select::SQL_ASC,
            ),
        );

        return $this;
    }

    /**
     * Stub method for compatibility with other search engines
     *
     * @return $this
     */
    public function setGeneralDefaultQuery()
    {
        return $this;
    }

    /**
     * Render sql select orders
     *
     * @return  Varien_Data_Collection_Db
     */
    protected function _renderOrders()
    {
        if (!$this->_isOrdersRendered) {
            foreach ($this->_orders as $attribute => $direction) {
                if ($attribute == self::RELEVANCE_ORDER_NAME) {
                    $this->_addRelevanceSorting();
                } else {
                    $this->addAttributeToSort($attribute, $direction);
                }
            }
            $this->_isOrdersRendered = true;
        }
        return $this;
    }
}

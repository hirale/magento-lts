<?php

/**
 * OpenMage
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available at https://opensource.org/license/osl-3-0-php
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2020 Magento, Inc. (https://www.magento.com)
 * @copyright  Copyright (c) 2022-2024 The OpenMage Contributors (https://www.openmage.org)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Label & link block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Urlrewrite_Link extends Mage_Core_Block_Abstract
{
    /**
     * Render output
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getItem()) {
            return '<p>' . $this->getLabel() . ' <a href="' . $this->getItemUrl() . '">'
                . $this->escapeHtml($this->getItem()->getName()) . '</a></p>';
        }
        return '';
    }
}

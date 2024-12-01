<?php
/**
 * OpenMage
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available at https://opensource.org/license/osl-3-0-php
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @copyright  Copyright (c) 2006-2020 Magento, Inc. (https://www.magento.com)
 * @copyright  Copyright (c) 2020-2024 The OpenMage Contributors (https://www.openmage.org)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog product option text type
 *
 * @category   Mage
 * @package    Mage_Catalog
 */
class Mage_Catalog_Model_Product_Option_Type_Text extends Mage_Catalog_Model_Product_Option_Type_Default
{
    public function getUserValue(): string
    {
        return (string) $this->getDataByKey('user_value');
    }

    /**
     * Validate user input for option
     *
     * @throws Mage_Core_Exception
     * @param array $values All product option values, i.e. array (option_id => mixed, option_id => mixed...)
     * @return $this
     */
    public function validateUserValue($values)
    {
        parent::validateUserValue($values);

        $option = $this->getOption();
        $value = trim($this->getUserValue());

        // Check requires option to have some value
        if (strlen($value) == 0 && $option->getIsRequire() && !$this->getSkipCheckRequiredOption()) {
            $this->setIsValid(false);
            Mage::throwException(Mage::helper('catalog')->__('Please specify the product required option <em>%s</em>.', $option->getTitle()));
        }

        // Check maximal length limit
        $maxCharacters = $option->getMaxCharacters();
        if ($maxCharacters > 0 && Mage::helper('core/string')->strlen($value) > $maxCharacters) {
            $this->setIsValid(false);
            Mage::throwException(Mage::helper('catalog')->__('The text is too long'));
        }

        $this->setUserValue($value);
        return $this;
    }

    /**
     * Prepare option value for cart
     *
     * @return string|null Prepared option value
     */
    public function prepareForCart()
    {
        if ($this->getIsValid() && strlen($this->getUserValue()) > 0) {
            return $this->getUserValue();
        } else {
            return null;
        }
    }

    /**
     * Return formatted option value for quote option
     *
     * @param string $value Prepared for cart option value
     * @return string
     */
    public function getFormattedOptionValue($value)
    {
        return Mage::helper('core')->escapeHtml($value);
    }
}

<?php

/**
 * OpenMage
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available at https://opensource.org/license/osl-3-0-php
 *
 * @category   Mage
 * @package    Mage_Rule
 * @copyright  Copyright (c) 2006-2020 Magento, Inc. (https://www.magento.com)
 * @copyright  Copyright (c) 2020-2025 The OpenMage Contributors (https://www.openmage.org)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class Mage_Rule_Model_Action_Collection
 *
 * @category   Mage
 * @package    Mage_Rule
 *
 * @method array getActions()
 * @method $this setActions(array $value)
 * @method $this setType(string $value)
 * @method Mage_Rule_Model_Abstract getRule()
 */
class Mage_Rule_Model_Action_Collection extends Mage_Rule_Model_Action_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setActions([]);
        $this->setType('rule/action_collection');
    }

    /**
     * Returns array containing actions in the collection
     *
     * Output example:
     * array(
     *   {action::asArray},
     *   {action::asArray}
     * )
     *
     * @return array
     */
    public function asArray(array $arrAttributes = [])
    {
        $out = parent::asArray();

        foreach ($this->getActions() as $item) {
            $out['actions'][] = $item->asArray();
        }
        return $out;
    }

    /**
     * @return $this|Mage_Rule_Model_Action_Abstract
     */
    public function loadArray(array $arr)
    {
        if (!empty($arr['actions']) && is_array($arr['actions'])) {
            foreach ($arr['actions'] as $actArr) {
                if (empty($actArr['type'])) {
                    continue;
                }
                $action = Mage::getModel($actArr['type']);
                $action->loadArray($actArr);
                $this->addAction($action);
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function addAction(Mage_Rule_Model_Action_Interface $action)
    {
        $actions = $this->getActions();

        $action->setRule($this->getRule());

        $actions[] = $action;
        if (!$action->getId()) {
            $action->setId($this->getId() . '.' . count($actions));
        }

        $this->setActions($actions);
        return $this;
    }

    /**
     * @return string
     */
    public function asHtml()
    {
        $html = $this->getTypeElement()->toHtml() . 'Perform following actions: ';
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }
        return $html;
    }

    /**
     * @return Varien_Data_Form_Element_Abstract
     */
    public function getNewChildElement()
    {
        $element = $this->getForm()->addField('action:' . $this->getId() . ':new_child', 'select', [
            'name' => 'rule[actions][' . $this->getId() . '][new_child]',
            'values' => $this->getNewChildSelectOptions(),
            'value_name' => $this->getNewChildName(),
        ]);

        $renderer = Mage::getBlockSingleton('rule/newchild');
        if ($renderer instanceof Varien_Data_Form_Element_Renderer_Interface) {
            $element->setRenderer($renderer);
        }

        return $element;
    }

    /**
     * @return string
     */
    public function asHtmlRecursive()
    {
        $html = $this->asHtml() . '<ul id="action:' . $this->getId() . ':children">';
        foreach ($this->getActions() as $cond) {
            $html .= '<li>' . $cond->asHtmlRecursive() . '</li>';
        }
        return $html . ('<li>' . $this->getNewChildElement()->getHtml() . '</li></ul>');
    }

    /**
     * @param string $format
     * @return string
     */
    public function asString($format = '')
    {
        return Mage::helper('rule')->__('Perform following actions');
    }

    /**
     * @param int $level
     * @return string
     */
    public function asStringRecursive($level = 0)
    {
        $str = $this->asString();
        foreach ($this->getActions() as $action) {
            $str .= "\n" . $action->asStringRecursive($level + 1);
        }
        return $str;
    }

    /**
     * @return $this|Mage_Rule_Model_Action_Abstract
     */
    public function process()
    {
        foreach ($this->getActions() as $action) {
            $action->process();
        }
        return $this;
    }
}

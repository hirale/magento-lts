<?php
/**
 * OpenMage
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available at https://opensource.org/license/osl-3-0-php
 *
 * @category    Tests
 * @package     Tests_Functional
 * @copyright  Copyright (c) 2006-2020 Magento, Inc. (https://www.magento.com)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Mage\CatalogRule\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Mage\CatalogRule\Test\Fixture\CatalogRule;
use Mage\CatalogRule\Test\Page\Adminhtml\CatalogRuleIndex;

/**
 * Assert that Catalog Price Rule is not presented in grid and cannot be found using ID, Rule name.
 */
class AssertCatalogPriceRuleNotInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that Catalog Price Rule is not presented in grid and cannot be found using ID, Rule name.
     *
     * @param CatalogRule $catalogPriceRule
     * @param CatalogRuleIndex $catalogRuleIndex
     * @return void
     */
    public function processAssert(CatalogRule $catalogPriceRule, CatalogRuleIndex $catalogRuleIndex)
    {
        $filter = [
            'rule_id' => $catalogPriceRule->getId(),
            'name' => $catalogPriceRule->getName(),
        ];
        $catalogRuleIndex->open();
        \PHPUnit\Framework\Assert::assertFalse(
            $catalogRuleIndex->getCatalogRuleGrid()->isRowVisible($filter),
            "Catalog Price Rule {$filter['rule_id']} with name {$filter['name']} is present in Catalog Price Rule grid."
        );
    }

    /**
     * Returns a string representation of successful assertion.
     *
     * @return string
     */
    public function toString()
    {
        return 'Catalog Price Rule is NOT present in Catalog Rule grid.';
    }
}

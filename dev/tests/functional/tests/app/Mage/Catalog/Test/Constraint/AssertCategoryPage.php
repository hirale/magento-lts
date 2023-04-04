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

namespace Mage\Catalog\Test\Constraint;

use Mage\Catalog\Test\Fixture\CatalogCategory;
use Mage\Catalog\Test\Page\Category\CatalogCategoryView;
use Magento\Mtf\Client\Browser;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Check that displayed category data on category page equals to passed from fixture.
 */
class AssertCategoryPage extends AbstractConstraint
{
    /**
     * Constraint severeness.
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that displayed category data on category page equals to passed from fixture.
     *
     * @param CatalogCategory $category
     * @param CatalogCategory $initialCategory
     * @param FixtureFactory $fixtureFactory
     * @param CatalogCategoryView $categoryView
     * @param Browser $browser
     * @return void
     */
    public function processAssert(
        CatalogCategory $category,
        CatalogCategory $initialCategory,
        FixtureFactory $fixtureFactory,
        CatalogCategoryView $categoryView,
        Browser $browser
    ) {
        $product = $fixtureFactory->createByCode(
            'catalogProductSimple',
            [
                'dataset' => 'default',
                'data' => [
                    'category_ids' => [
                        'category' => $initialCategory
                    ]
                ]
            ]
        );
        $categoryData = array_merge($initialCategory->getData(), $category->getData());
        $product->persist();
        $url = $_ENV['app_frontend_url'] . strtolower($category->getUrlKey()) . '.html';
        $browser->open($url);
        \PHPUnit\Framework\Assert::assertEquals(
            $url,
            $browser->getUrl(),
            'Wrong page URL.'
        );

        if (isset($categoryData['name'])) {
            \PHPUnit\Framework\Assert::assertEquals(
                strtoupper($categoryData['name']),
                $categoryView->getTitleBlock()->getTitle(),
                'Wrong page title.'
            );
        }

        if (isset($categoryData['description'])) {
            \PHPUnit\Framework\Assert::assertEquals(
                $categoryData['description'],
                $categoryView->getViewBlock()->getDescription(),
                'Wrong category description.'
            );
        }

        if (isset($categoryData['default_sort_by'])) {
            $sortBy = strtolower($categoryData['default_sort_by']);
            $sortType = $categoryView->getTopToolbar()->getSelectSortType();
            \PHPUnit\Framework\Assert::assertEquals(
                $sortBy,
                $sortType,
                'Wrong sorting type.'
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Category data on category page equals to passed from fixture.';
    }
}

<?php

/**
 * OpenMage
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available at https://opensource.org/license/osl-3-0-php
 *
 * @category   OpenMage
 * @package    OpenMage_Tests
 * @copyright  Copyright (c) 2024 The OpenMage Contributors (https://www.openmage.org)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace OpenMage\Tests\Unit\Mage\Adminhtml\Block\System\Convert\Gui\Edit\Tab;

use Mage;
use Mage_Adminhtml_Block_System_Convert_Gui_Edit_Tab_View as Subject;
use Mage_Dataflow_Model_Profile;
use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    public Subject $subject;

    public function setUp(): void
    {
        Mage::app();
        $this->subject = new Subject();
    }

    /**
     * @group Mage_Adminhtml
     * @group Mage_Adminhtml_Block
     */
    public function testInitForm(): void
    {
        $mock = $this->getMockBuilder(Subject::class)
            ->setMethods(['getRegistryCurrentConvertProfile'])
            ->getMock();

        $mock
            ->method('getRegistryCurrentConvertProfile')
            ->willReturn(new Mage_Dataflow_Model_Profile());

        $this->assertInstanceOf(Subject::class, $mock->initForm());
    }
}

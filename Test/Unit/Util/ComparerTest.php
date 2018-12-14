<?php
/**
 * Yireo TaxRatesManager2 extension for Magento 2
 *
 * @package     Yireo_TaxRatesManager2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2018 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Test\Unit\Util;

use PHPUnit\Framework\TestCase;
use Yireo\TaxRatesManager2\Util\Comparer as Target;

/**
 * Class ComparerTest
 */
class ComparerTest extends TestCase
{
    /**
     *
     */
    public function testGetSmallestDifference()
    {
        $target = new Target();
        $this->assertSame(2.0, $target->getSmallestDifference(4.0, 2.0, 7.0));
        $this->assertSame(3.0, $target->getSmallestDifference(4.0, 2.0, 3.0));
        $this->assertSame(2.0, $target->getSmallestDifference(4.0, 2.0, 2.0));
        $this->assertSame(4.0, $target->getSmallestDifference(4.0, 4.0, 2.0));
    }
}

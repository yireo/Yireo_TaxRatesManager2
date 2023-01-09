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

namespace Yireo\TaxRatesManager2\Test\Unit\Rate;

use PHPUnit\Framework\TestCase;
use Yireo\TaxRatesManager2\Rate\Rate as Target;

/**
 * Class RateTest
 * @package Yireo\TaxRatesManager2\Test\Unit\Rate
 */
class RateTest extends TestCase
{
    public function testRateCreation()
    {
        $target = new Target(42, 'foobar', 'fr', '*', 2.0);
        $this->assertSame(42, $target->getId());
        $this->assertSame('foobar', $target->getCode());
        $this->assertSame('fr', $target->getCountryId());
        $this->assertSame('*', $target->getPostcode(), '*');
        $this->assertSame(2.0, $target->getPercentage());
    }
}

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
use Yireo\TaxRatesManager2\Util\CommandLine as Target;

/**
 * Class CommandLineTest
 * @package Yireo\TaxRatesManager2\Test\Unit\Util
 */
class CommandLineTest extends TestCase
{
    public function testIsCli()
    {
        $target = new Target();
        $this->assertTrue($target->isCli());
    }
}

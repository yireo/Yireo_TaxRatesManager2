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

namespace Yireo\TaxRatesManager2\Test\Functional\Check;

use PHPUnit\Framework\TestCase;
use Yireo\TaxRatesManager2\Check\Check;

/**
 * Class CheckTest
 */
class CheckTest extends TestCase
{
    /**
     *
     */
    public function testCurrentSituation()
    {
        $check = $this->getCheck();
        $this->assertTrue(true);
    }

    /**
     * @return Check
     */
    private function getCheck(): Check
    {
    }
}

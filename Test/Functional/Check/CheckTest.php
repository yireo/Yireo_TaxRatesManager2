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

use Yireo\TaxRatesManager2\Provider\StoredRates;
use Yireo\TaxRatesManager2\Test\Utils\AbstractTestCase;
use Yireo\TaxRatesManager2\Check\Check;

/**
 * Class CheckTest
 */
class CheckTest extends AbstractTestCase
{
    /**
     *
     */
    public function testWhetherCheckResultsInNewRates()
    {
        $currentRates = $this->getStoredRatesProvider()->getRates();
        $currentRatesCount = count($currentRates);

        $check = $this->getCheck();
        $check->execute();

        $newRates = $this->getStoredRatesProvider()->getRates();
        $newRatesCount = count($newRates);
        $this->assertGreaterThan($newRatesCount, $currentRatesCount);
    }

    /**
     * @return Check
     */
    private function getCheck(): Check
    {
        return $this->getObjectManager()->get(Check::class);
    }

    /**
     * @return StoredRates
     */
    private function getStoredRatesProvider(): StoredRates
    {
        return $this->getObjectManager()->get(StoredRates::class);
    }
}

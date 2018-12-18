<?php
declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Test\Integration\Provider;

use Yireo\TaxRatesManager2\Provider\StoredRates;
use Yireo\TaxRatesManager2\Test\Integration\TestCase;

/**
 * Class StoredRatesTest
 * @package Yireo\TaxRatesManager2\Test\Integration\Provider
 */
class StoredRatesTest extends TestCase
{
    public function testIfUsRatesExistByDefault()
    {
        /** @var StoredRates $storedRates */
        $storedRates = $this->getObjectManager()->get(StoredRates::class);
        $rates = (array) $storedRates->getRates();
        $this->assertNotEmpty($rates);
    }
}

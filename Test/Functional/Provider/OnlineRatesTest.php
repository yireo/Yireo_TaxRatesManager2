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

namespace Yireo\TaxRatesManager2\Test\Functional\Provider;

use GuzzleHttp\Exception\ClientException;
use Yireo\TaxRatesManager2\Provider\OnlineRates as Target;
use Yireo\TaxRatesManager2\Test\Utils\AbstractTestCase as TestCase;

/**
 * Class OnlineRatesTest
 */
class OnlineRatesTest extends TestCase
{
    /**
     * Test whether the rates could be fetched in a default situation
     */
    public function testRateFetchingByDefault()
    {
        $onlineRatesProvider = $this->getTarget();
        $onlineRates = $onlineRatesProvider->getRates();
        $this->assertNotEmpty($onlineRates);
    }

    /**
     * Test whether the rates could be fetched in a default situation
     * @magentoConfigFixture taxratesmanager/settings/feed_source ""
     */
    public function testRateFetchingWithEmptyDefaultDefault()
    {
        $onlineRatesProvider = $this->getTarget();
        $onlineRates = $onlineRatesProvider->getRates();
        $this->assertNotEmpty($onlineRates);
    }

    /**
     * Test whether the fetching crashes when an invalid alternative feed is set
     * @expectedException ClientException
     * @magentoConfigFixture taxratesmanager/settings/alternative_feed_source not_even_a_url
     */
    public function testRateFetchingWithInvalidFeed()
    {
        $onlineRatesProvider = $this->getTarget();
        $onlineRates = $onlineRatesProvider->getRates();
        $this->assertNotEmpty($onlineRates);
    }

    /**
     * @return Target
     */
    private function getTarget(): Target
    {
        return $this->getObjectManager()->get(Target::class);
    }
}

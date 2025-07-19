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

namespace Yireo\TaxRatesManager2\Test\Utils;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * @magentoConfigFixture taxratesmanager/settings/fix_automatically_in_backend 0
 * @magentoConfigFixture taxratesmanager/settings/fix_automatically_in_cron 0
 * @magentoConfigFixture taxratesmanager/settings/send_email 0
 * @magentoConfigFixture taxratesmanager/settings/email info@example.org
 * @magentoConfigFixture taxratesmanager/settings/feed_source tax_rates_eu.csv
 * @magentoConfigFixture taxratesmanager/settings/alternative_feed_source
 * @magentoConfigFixture taxratesmanager/settings/update_name 0
 */
class AbstractTestCase extends TestCase
{
    /**
     * @return ScopeConfigInterface
     */
    protected function getScopeConfig(): ScopeConfigInterface
    {
        return $this->getObjectManager()->get(ScopeConfigInterface::class);
    }

    /**
     * @return ObjectManager
     */
    protected function getObjectManager() : ObjectManager
    {
        return ObjectManager::getInstance();
    }
}

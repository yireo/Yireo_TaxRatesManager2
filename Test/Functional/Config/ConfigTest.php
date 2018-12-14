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

use Yireo_TaxRatesManager_Config_Config as Target;
use Yireo_TaxRatesManager_Test_Utils_AbstractTestCase as TestCase;

/**
 * Class Yireo_TaxRatesManager_Test_Functional_Config_ConfigTest
 */
class Yireo_TaxRatesManager_Test_Functional_Config_ConfigTest extends TestCase
{
    /**
     * @throws Mage_Core_Model_Store_Exception
     */
    public function testFixAutomatically()
    {
        $this->setConfigValue('fix_automatically_in_cron', 1);
        $this->assertTrue($this->getTarget()->fixAutomatically());

        $this->setConfigValue('fix_automatically_in_cron', 0);
        $this->assertFalse($this->getTarget()->fixAutomatically());
    }

    /**
     * @throws Mage_Core_Model_Store_Exception
     */
    public function testSendEmail()
    {
        $this->setConfigValue('send_email', 1);
        $this->assertTrue($this->getTarget()->sendEmail());

        $this->setConfigValue('send_email', 0);
        $this->assertFalse($this->getTarget()->sendEmail());
    }

    /**
     * @throws Mage_Core_Model_Store_Exception
     */
    public function testEmail()
    {
        $this->setConfigValue('email', 'info@example.org');
        $this->assertSame('info@example.org', $this->getTarget()->email());
    }

    /**
     * @throws Mage_Core_Model_Store_Exception
     */
    public function testGetFeedUrl()
    {
        $this->setConfigValue('alternative_feed_source', 'alt_foobar');
        $this->assertSame('alt_foobar', $this->getTarget()->getFeedUrl());

        $this->setConfigValue('alternative_feed_source', '');
        $this->setConfigValue('feed_source', 'foobar');
        $this->assertSame(Target::PREFIX.'foobar', $this->getTarget()->getFeedUrl());
    }

    /**
     * @throws Mage_Core_Model_Store_Exception
     */
    public function testUpdateNameFromExistingItems()
    {
        $this->setConfigValue('update_name', 1);
        $this->assertTrue($this->getTarget()->updateNameFromExistingItems());

        $this->setConfigValue('update_name', 0);
        $this->assertFalse($this->getTarget()->updateNameFromExistingItems());
    }

    /**
     * @return Yireo_TaxRatesManager_Config_Config
     */
    private function getTarget(): Target
    {
        return $this->getFactory()->get(Yireo_TaxRatesManager_Config_Config::class);
    }
}
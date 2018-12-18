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

namespace Yireo\TaxRatesManager2\Test\Functional\Config;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Yireo\TaxRatesManager2\Config\Config as Target;
use Yireo\TaxRatesManager2\Test\Utils\AbstractTestCase;

/**
 * Class ConfigTest
 */
class ConfigTest extends AbstractTestCase
{
    /**
     */
    public function testFixAutomatically()
    {
        $this->setConfigValue('fix_automatically_in_cron', 1);
        $this->assertTrue($this->getTarget()->fixAutomatically());

        $this->setConfigValue('fix_automatically_in_cron', 0);
        $this->assertFalse($this->getTarget()->fixAutomatically());
    }

    /**
     */
    public function testSendEmail()
    {
        $this->setConfigValue('send_email', 1);
        $this->assertTrue($this->getTarget()->sendEmail());

        $this->setConfigValue('send_email', 0);
        $this->assertFalse($this->getTarget()->sendEmail());
    }

    /**
     */
    public function testEmail()
    {
        $this->setConfigValue('email', 'info@example.org');
        $this->assertSame('info@example.org', $this->getTarget()->email());
    }

    /**
     */
    public function testGetFeedUrl()
    {
        $this->setConfigValue('alternative_feed_source', 'alt_foobar');
        $this->assertSame('alt_foobar', $this->getTarget()->getFeedUrl());

        $this->setConfigValue('alternative_feed_source', '');
        $this->setConfigValue('feed_source', 'foobar');
        $this->assertSame(Target::PREFIX.'foobar', $this->getTarget()->getFeedUrl());
    }

    public function testUpdateNameFromExistingItems()
    {
        $this->setConfigValue('update_name', 1);
        $this->assertTrue($this->getTarget()->updateNameFromExistingItems());

        $this->setConfigValue('update_name', 0);
        $this->assertFalse($this->getTarget()->updateNameFromExistingItems());
    }

    /**
     * @return Target
     */
    private function getTarget(): Target
    {
        return $this->getObjectManager()->get(Target::class);
    }

    /**
     * @param string $path
     * @param $value
     * @param string $pathPrefix
     */
    private function setConfigValue(string $path, $value, string $pathPrefix = 'taxratesmanager2/settings/')
    {
        $path = $pathPrefix . $path;
        $configWriter = $this->getObjectManager()->get(WriterInterface::class);
        $configWriter->save($path, $value);
    }
}

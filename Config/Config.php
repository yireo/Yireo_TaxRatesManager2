<?php
/**
 * TaxRatesManager2 module for Magento
 *
 * @package     Yireo_TaxRatesManager2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2018 Yireo (https://www.yireo.com/)
 * @license     Open Source License 3
 */

declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Yireo\TaxRatesManager2\Util\CommandLine;

/**
 * Class Config
 */
class Config
{
    const PREFIX = 'https://raw.githubusercontent.com/yireo/Magento_EU_Tax_Rates/master/';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CommandLine
     */
    private $cli;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param CommandLine $cli
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CommandLine $cli
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->cli = $cli;
    }

    /**
     * @return bool
     */
    public function fixAutomatically(): bool
    {
        if ($this->cli->isCli()) {
            return (bool)$this->getModuleConfig('fix_automatically_in_cron');
        }

        return (bool)$this->getModuleConfig('fix_automatically_in_backend');
    }

    /**
     * @return bool
     */
    public function sendEmail(): bool
    {
        return (bool)$this->getModuleConfig('send_email');
    }

    /**
     * @return string
     */
    public function email(): string
    {
        $email = (string)$this->getModuleConfig('email');
        if (!empty($email)) {
            return $email;
        }

        return (string)$this->getModuleConfig('email', 'trans_email/ident_general');
    }

    /**
     * @return string
     */
    public function getFeedUrl(): string
    {
        $alternativeFeed = (string)$this->getModuleConfig('alternative_feed_source');
        if ($alternativeFeed) {
            return $alternativeFeed;
        }

        $feed = (string)$this->getModuleConfig('feed_source');
        if (!empty($feed)) {
            return self::PREFIX . $feed;
        }

        return self::PREFIX . 'tax_rates_eu.csv';
    }

    /**
     * @return bool
     */
    public function updateNameFromExistingItems(): bool
    {
        return (bool)$this->getModuleConfig('update_name');
    }

    /**
     * @return bool
     */
    public function allowCache(): bool
    {
        if ($this->cli->isCli()) {
            return false;
        }

        return (bool)$this->getModuleConfig('cache');
    }

    /**
     * @param string $path
     * @param string $prefix
     * @return string|null
     */
    private function getModuleConfig(string $path, string $prefix = '')
    {
        if (empty($prefix)) {
            $prefix = 'taxratesmanager/settings';
        }

        $prefix = preg_replace('/\/$/', '', $prefix);

        return $this->scopeConfig->getValue($prefix . '/' . $path);
    }
}

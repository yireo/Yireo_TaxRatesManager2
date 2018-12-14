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

namespace Yireo\TaxRatesManager2\Check;

use Yireo\TaxRatesManager2\Config\Config;
use Yireo\TaxRatesManager2\Util\Comparer;
use Yireo\TaxRatesManager2\Api\LoggerInterface as Logger;
use Yireo\TaxRatesManager2\Provider\OnlineRates as OnlineRatesProvider;
use Yireo\TaxRatesManager2\Provider\StoredRates as StoredRatesProvider;
use Yireo\TaxRatesManager2\Rate\Rate;

/**
 * Class Check
 */
class Check
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var OnlineRatesProvider
     */
    private $onlineRatesProvider;

    /**
     * @var StoredRatesProvider
     */
    private $storedRatesProvider;

    /**
     * @var Comparer
     */
    private $comparer;

    /**
     * @var int
     */
    private $verbosity;

    /**
     * Yireo_TaxRatesManager_Provider constructor.
     * @param Config $config
     * @param Logger $logger
     * @param OnlineRatesProvider $onlineRatesProvider
     * @param StoredRatesProvider $storedRatesProvider
     * @param Comparer $comparer
     * @param int $verbosity
     */
    public function __construct(
        Config $config,
        Logger $logger,
        OnlineRatesProvider $onlineRatesProvider,
        StoredRatesProvider $storedRatesProvider,
        Comparer $comparer,
        int $verbosity = 0
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->onlineRatesProvider = $onlineRatesProvider;
        $this->storedRatesProvider = $storedRatesProvider;
        $this->comparer = $comparer;
        $this->verbosity = $verbosity;
    }

    /**
     * Main function
     * @return bool
     */
    public function execute(): bool
    {
        $storedRates = $this->storedRatesProvider->getRates();
        if (empty($storedRates)) {
            $this->logger->warning('No stored rates found');
        }

        if ($this->verbosity >= 1) {
            foreach ($storedRates as $storedRate) {
                $msg = sprintf(
                    'Found stored rate: %s = %s',
                    $storedRate->getCode(),
                    $storedRate->getPercentage()
                );

                $this->logger->info($msg);
            }
        }

        $onlineRates = $this->onlineRatesProvider->getRates();
        if (empty($storedRates)) {
            $this->logger->warning('No online rates found');
        }

        if ($this->verbosity >= 1) {
            foreach ($onlineRates as $onlineRate) {
                $msg = sprintf(
                    'Found online rate: %s = %s',
                    $onlineRate->getCode(),
                    $onlineRate->getPercentage()
                );

                $this->logger->info($msg);
            }
        }

        $this->checkMatches($storedRates, $onlineRates);

        return true;
    }

    /**
     * @param int $verbosity
     */
    public function setVerbosity(int $verbosity)
    {
        $this->verbosity = $verbosity;
    }

    /**
     * @param Rate[] $storedRates
     * @param Rate[] $onlineRates
     */
    private function checkMatches(array $storedRates, array $onlineRates)
    {
        foreach ($storedRates as $storedRate) {
            $this->checkStoredRate($storedRate, $onlineRates);
        }

        foreach ($onlineRates as $onlineRate) {
            $this->checkOnlineRate($onlineRate, $storedRates);
        }
    }

    /**
     * @param Rate $storedRate
     * @param Rate[] $onlineRates
     * @return bool
     */
    private function checkStoredRate(Rate $storedRate, array $onlineRates): bool
    {
        $suggestRate = 0;
        foreach ($onlineRates as $onlineRate) {
            if ($onlineRate->getCountryId() !== $storedRate->getCountryId()) {
                continue;
            }

            $suggestRate = $this->comparer->getSmallestDifference(
                $storedRate->getPercentage(),
                $onlineRate->getPercentage(),
                $suggestRate
            );

            if ($this->verbosity >= 2) {
                $msg = sprintf(
                    'Comparing %s rate %d%% with %d%%',
                    $onlineRate->getCountryId(),
                    $onlineRate->getPercentage(),
                    $storedRate->getPercentage()
                );

                $this->logger->info($msg);
            }

            if ($onlineRate->getPercentage() !== $storedRate->getPercentage()) {
                continue;
            }

            return true;
        }

        $msg = sprintf(
            'Existing rate "%s" (%s%%) seems incorrect.',
            $storedRate->getCode(),
            $storedRate->getPercentage()
        );

        if ($this->config->fixAutomatically()) {
            $storedRate->setPercentage($suggestRate);
            $this->storedRatesProvider->saveRate($storedRate);
            $msg = sprintf(
                'Automatically corrected existing rate to %s%%: %s',
                $suggestRate,
                $storedRate->getCode()
            );

            $this->logger->success($msg);
            return true;
        }

        if ($suggestRate > 0) {
            $msg .= ' ' . sprintf('Perhaps it should be %s%%?', $suggestRate);
        } else {
            $msg .= ' Perhaps it should be removed or empty?';
        }

        $this->logger->warning($msg);
        return false;
    }

    /**
     * @param Rate $onlineRate
     * @param Rate[] $storedRates
     * @return bool
     */
    private function checkOnlineRate(Rate $onlineRate, array $storedRates): bool
    {
        if (!$onlineRate->getPercentage() > 0) {
            return false;
        }

        $foundMatch = false;

        foreach ($storedRates as $storedRate) {
            if ($storedRate->getCode() === $onlineRate->getCode()) {
                $foundMatch = true;
                break;
            }

            if ($storedRate->getCountryId() !== $onlineRate->getCountryId()) {
                continue;
            }

            if ($storedRate->getPercentage() !== $onlineRate->getPercentage()) {
                continue;
            }

            $foundMatch = true;
            break;
        }

        if ($foundMatch) {
            return false;
        }

        $this->logger->warning(sprintf(
            'A new rate "%s" (%s%%) is not configured in your store yet [%s]',
            $onlineRate->getCode(),
            $onlineRate->getPercentage(),
            $onlineRate->getCountryId()
        ));

        if ($this->config->fixAutomatically()) {
            $this->storedRatesProvider->saveRate($onlineRate);
            $msg = sprintf(
                'Automatically saved a new rate %s: %s',
                $onlineRate->getPercentage(),
                $onlineRate->getCode()
            );

            $this->logger->success($msg);
        }

        return true;
    }
}

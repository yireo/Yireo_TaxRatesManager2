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

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Yireo\TaxRatesManager2\Config\Config;
use Yireo\TaxRatesManager2\Util\Comparer;
use Yireo\TaxRatesManager2\Api\LoggerInterface as Logger;
use Yireo\TaxRatesManager2\Provider\OnlineRates as OnlineRatesProvider;
use Yireo\TaxRatesManager2\Provider\StoredRates as StoredRatesProvider;
use Yireo\TaxRatesManager2\Rate\Rate;
use Magento\Customer\Model\Vat as VatModel;

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
     * @var bool
     */
    private $fixAutomatically = false;

    /**
     * @var VatModel
     */
    private $vatModel;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * Yireo_TaxRatesManager_Provider constructor.
     * @param Config $config
     * @param Logger $logger
     * @param OnlineRatesProvider $onlineRatesProvider
     * @param StoredRatesProvider $storedRatesProvider
     * @param Comparer $comparer
     * @param VatModel $vatModel
     * @param UrlInterface $url
     * @param int $verbosity
     */
    public function __construct(
        Config $config,
        Logger $logger,
        OnlineRatesProvider $onlineRatesProvider,
        StoredRatesProvider $storedRatesProvider,
        Comparer $comparer,
        VatModel $vatModel,
        UrlInterface $url,
        int $verbosity = null
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->onlineRatesProvider = $onlineRatesProvider;
        $this->storedRatesProvider = $storedRatesProvider;
        $this->comparer = $comparer;
        $this->verbosity = $verbosity;
        $this->vatModel = $vatModel;
        $this->url = $url;
    }

    /**
     * Main function
     *
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
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
     * Set the verbosity flag manually
     *
     * @param int $verbosity
     */
    public function setVerbosity(int $verbosity)
    {
        $this->verbosity = $verbosity;
    }

    /**
     * Set the the fix-automatically flag manually
     *
     * @param bool $fixAutomatically
     */
    public function setFixAutomatically(bool $fixAutomatically)
    {
        $this->fixAutomatically = $fixAutomatically;
    }

    /**
     * @param Rate[] $storedRates
     * @param Rate[] $onlineRates
     * @throws InputException
     * @throws NoSuchEntityException
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
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function checkStoredRate(Rate $storedRate, array $onlineRates): bool
    {
        // @todo: If US rates are supported as well, this needs to be refactored
        if (!$this->isCountryInEu($storedRate->getCountryId())) {
            return true;
        }

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

        if ($this->fixAutomatically || $this->config->fixAutomatically()) {
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

        $msg .= ' (<a href="' . $this->getFixUrl($storedRate) . '">Fix this now</a>)';

        $this->logger->warning($msg);
        return false;
    }

    /**
     * @param Rate $rate
     * @return string
     */
    private function getFixUrl(Rate $rate): string
    {
        return $this->url->getUrl('taxratesmanager/index/fix', ['id' => $rate->getId()]);
    }

    /**
     * @param Rate $onlineRate
     * @param Rate[] $storedRates
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
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

        $msg = sprintf(
            'A new rate "%s" (%s%%) is not configured in your store yet [%s]',
            $onlineRate->getCode(),
            $onlineRate->getPercentage(),
            $onlineRate->getCountryId()
        );

        $msg .= ' (<a href="' . $this->getAddUrl($onlineRate) . '">Add</a>)';
        $this->logger->warning($msg);

        if ($this->fixAutomatically || $this->config->fixAutomatically()) {
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

    /**
     * Get the URL for adding a new rate
     *
     * @param Rate $rate
     * @return string
     */
    private function getAddUrl(Rate $rate): string
    {
        $data = [
            'country' => $rate->getCountryId(),
            'percentage' => $rate->getPercentage(),
        ];

        return $this->url->getUrl('taxratesmanager/index/add', $data);
    }

    /**
     * Check whether a specific country is in the EU or not
     *
     * @param string $countryId
     * @return bool
     */
    public function isCountryInEu(string $countryId): bool
    {
        return (bool)$this->vatModel->isCountryInEU($countryId);
    }
}

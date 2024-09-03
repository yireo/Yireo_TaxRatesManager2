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

namespace Yireo\TaxRatesManager2\Provider;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Magento\Framework\App\Cache;
use Magento\Framework\Serialize\Serializer\Json;
use Yireo\TaxRatesManager2\Config\Config;
use Yireo\TaxRatesManager2\Rate\Rate;

/**
 * Class OnlineRates
 */
class OnlineRates
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $columns = [
        'code' => 'Code',
        'country' => 'Country',
        'state' => 'State',
        'zip' => 'Zip/Post Code',
        'rate' => 'Rate',
        'zip_is_range' => 'Zip/Post is Range',
        'range_from' => 'Range From',
        'range_to' => 'Range To',
        'default' => 'default'
    ];
    /**
     * @var Cache
     */
    private $cache;
    /**
     * @var Client
     */
    private $client;
    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * OnlineRates constructor.
     * @param Config $config
     * @param Cache $cache
     * @param Client $client
     * @param Json $jsonSerializer
     */
    public function __construct(
        Config $config,
        Cache $cache,
        Client $client,
        Json $jsonSerializer
    ) {
        $this->config = $config;
        $this->cache = $cache;
        $this->client = $client;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @return Rate[]
     */
    public function getRates(): array
    {
        $rates = [];
        $onlineRates = $this->getRatesFromCacheOrOnline();

        foreach ($onlineRates as $onlineRate) {
            $rates[] = new Rate(
                0,
                (string)$onlineRate['code'],
                (string)$onlineRate['country'],
                (string)$onlineRate['zip'],
                (float)$onlineRate['rate']
            );
        }

        return $rates;
    }

    /**
     * @return array
     */
    private function getRatesFromCacheOrOnline(): array
    {
        if ($this->config->allowCache()) {
            $rates = $this->loadFromCache();
            if (!empty($rates)) {
                return $rates;
            }
        }

        $rates = $this->loadFromOnline();
        $this->saveToCache($rates);

        return $rates;
    }

    /**
     * @return array
     */
    private function loadFromCache(): array
    {
        if ($data = $this->cache->load($this->getCacheId())) {
            $data = $this->jsonSerializer->unserialize($data);
        }

        if (!empty($data) && is_array($data)) {
            return $data;
        }

        return [];
    }

    /**
     * @return array
     */
    private function loadFromOnline(): array
    {
        $response = $this->client->get($this->config->getFeedUrl());
        $contents = (string) $response->getBody();
        $rows = array_map('str_getcsv', explode("\n", $contents));
        $headerRow = array_shift($rows);
        $this->validateHeaderRow($headerRow);
        $rates = [];

        foreach ($rows as $row) {
            if (empty($row[0])) {
                continue;
            }
            $i = 0;
            $rate = [];
            foreach ($this->columns as $columnCode => $columnName) {
                $rate[$columnCode] = $row[$i] ?? '';
                $i++;
            }
            $rates[] = $rate;
        }

        return $rates;
    }

    /**
     * @return string
     */
    private function getCacheId(): string
    {
        $feedUrl = $this->config->getFeedUrl();
        return 'TAXRATESMANAGER_' . md5($feedUrl); // phpcs:ignore
    }

    /**
     * @param $data
     */
    private function saveToCache($data)
    {
        $cacheTags = [];
        $this->cache->save($this->jsonSerializer->serialize($data), $this->getCacheId(), $cacheTags);
    }

    /**
     * @param array $headerRow
     * @return bool
     */
    private function validateHeaderRow(array $headerRow): bool
    {
        if (count($headerRow) !== count($this->columns)) {
            $msg = 'CSV header is of unexpected size: ' . var_export($headerRow, true);
            throw new InvalidArgumentException($msg);
        }

        $i = 0;
        foreach ($this->columns as $columnCode => $columnName) {
            if ($headerRow[$i] != $columnName) {
                $msg = sprintf('CSV header contains unexpected value "%s" at position %d', $headerRow[$i], $i);
                throw new InvalidArgumentException($msg);
            }
            $i++;
        }

        return true;
    }
}

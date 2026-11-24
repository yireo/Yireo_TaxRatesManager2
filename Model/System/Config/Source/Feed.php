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

namespace Yireo\TaxRatesManager2\Model\System\Config\Source;

use GuzzleHttp\Client;
use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Throwable;
use Yireo\TaxRatesManager2\Config\Config;

/**
 * Class Feed
 */
class Feed implements ArrayInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * Feed constructor.
     * @param Client $client
     * @param Json $jsonSerializer
     */
    public function __construct(
        Client $client,
        Json $jsonSerializer
    ) {
        $this->client = $client;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];

        foreach ($this->getSources() as $source) {
            $options[] = [
                'value' => $source,
                'label' => $source,
            ];
        }

        return $options;
    }

    /**
     * @return string[]
     */
    private function getSources(): array
    {
        try {
            $response = $this->client->get(Config::PREFIX . 'feeds.json', [
                'timeout' => 10,
                'connect_timeout' => 5,
                'allow_redirects' => false,
            ]);
            $data = $this->jsonSerializer->unserialize((string)$response->getBody());
        } catch (Throwable $exception) {
            return [Config::DEFAULT_FEED];
        }

        if (!is_array($data) || !isset($data['feeds']) || !is_array($data['feeds'])) {
            return [Config::DEFAULT_FEED];
        }

        $sources = array_filter(
            $data['feeds'],
            fn ($source) => is_string($source) && preg_match(Config::FEED_SOURCE_PATTERN, $source) === 1
        );

        return !empty($sources) ? array_values($sources) : [Config::DEFAULT_FEED];
    }
}

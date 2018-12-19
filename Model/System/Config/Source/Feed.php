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
        $response = $this->client->get(Config::PREFIX.'/feeds.json');
        $contents = $response->getBody();
        $data = $this->jsonSerializer->unserialize($contents);

        return $data['feeds'];
    }
}

<?php
declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Test\Functional\Model\System\Config\Source;

use Yireo\TaxRatesManager2\Model\System\Config\Source\Feed;
use Yireo\TaxRatesManager2\Test\Utils\AbstractTestCase as TestCase;

/**
 * Class FeedTest
 * @package Yireo\TaxRatesManager2\Test\Functional\Model\System\Config\Source
 */
class FeedTest extends TestCase
{
    public function testToOptionArray()
    {
        /** @var Feed $feed */
        $feed = $this->getObjectManager()->get(Feed::class);
        $this->assertNotEmpty($feed->toOptionArray());
    }
}

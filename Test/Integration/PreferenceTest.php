<?php
declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Test\Integration;

use Yireo\TaxRatesManager2\Api\LoggerInterface;
use Yireo\TaxRatesManager2\Logger\Console;

/**
 * Class PreferenceTest
 * @package Yireo\TaxRatesManager2\Test\Integration
 */
class PreferenceTest extends TestCase
{
    public function testIfLoggerInterfaceMapsToPreference()
    {
        $logger = $this->getObjectManager()->get(LoggerInterface::class);
        $this->assertInstanceOf(Console::class, $logger);
    }
}

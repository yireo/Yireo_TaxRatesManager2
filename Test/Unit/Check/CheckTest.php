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

namespace Yireo\TaxRatesManager2\Test\Unit\Check;

use Magento\Customer\Model\Vat as VatModel;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yireo\TaxRatesManager2\Api\LoggerInterface;
use Yireo\TaxRatesManager2\Check\Check;
use Yireo\TaxRatesManager2\Config\Config;
use Yireo\TaxRatesManager2\Provider\OnlineRates;
use Yireo\TaxRatesManager2\Provider\StoredRates;
use Yireo\TaxRatesManager2\Rate\Rate;
use Yireo\TaxRatesManager2\Util\Comparer;

class CheckTest extends TestCase
{
    public function testWarnsAboutMissingOnlineRatesOnly()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')->with('No online rates found');

        $this->createCheck($logger, [$this->createStub(Rate::class)], [])->execute();
    }

    public function testWarnsAboutMissingStoredRatesOnly()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')->with('No stored rates found');

        $this->createCheck($logger, [], [$this->createStub(Rate::class)])->execute();
    }

    /**
     * @param LoggerInterface&MockObject $logger
     * @param Rate[] $storedRates
     * @param Rate[] $onlineRates
     */
    private function createCheck(LoggerInterface $logger, array $storedRates, array $onlineRates): Check
    {
        $storedRatesProvider = $this->createStub(StoredRates::class);
        $storedRatesProvider->method('getRates')->willReturn($storedRates);

        $onlineRatesProvider = $this->createStub(OnlineRates::class);
        $onlineRatesProvider->method('getRates')->willReturn($onlineRates);

        return new Check(
            $this->createStub(Config::class),
            $logger,
            $onlineRatesProvider,
            $storedRatesProvider,
            $this->createStub(Comparer::class),
            $this->createStub(VatModel::class),
            $this->createStub(UrlInterface::class),
            0
        );
    }
}

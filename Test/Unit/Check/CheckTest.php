<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Test\Unit\Check;

use Magento\Customer\Model\Vat as VatModel;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\TestCase;
use Yireo\TaxRatesManager2\Api\LoggerInterface;
use Yireo\TaxRatesManager2\Check\Check;
use Yireo\TaxRatesManager2\Config\Config;
use Yireo\TaxRatesManager2\Logger\ActionLoggerInterface;
use Yireo\TaxRatesManager2\Provider\OnlineRates;
use Yireo\TaxRatesManager2\Provider\StoredRates;
use Yireo\TaxRatesManager2\Rate\Rate;
use Yireo\TaxRatesManager2\Util\Comparer;

class CheckTest extends TestCase
{
    public function testIncorrectStoredRateSendsPlainTextWarningWithFixAction()
    {
        $logger = $this->createMock(ActionLoggerTestDouble::class);
        $logger->expects($this->never())->method('warning');
        $logger->expects($this->once())->method('warningWithAction')->with(
            $this->logicalNot($this->stringContains('<a ')),
            [
                'url' => 'https://example.com/taxratesmanager/index/fix/',
                'label' => 'Fix this now',
                'params' => ['id' => 42],
            ]
        );

        $storedRate = new Rate(42, '<img src=x>', 'NL', '*', 19.0);
        $this->createCheck($logger)->checkStoredRate($storedRate, [new Rate(0, 'NL', 'NL', '*', 21.0)]);
    }

    public function testMissingOnlineRateSendsAddActionWithPostParams()
    {
        $logger = $this->createMock(ActionLoggerTestDouble::class);
        $logger->expects($this->once())->method('warningWithAction')->with(
            $this->logicalNot($this->stringContains('<a')),
            [
                'url' => 'https://example.com/taxratesmanager/index/add/',
                'label' => 'Add',
                'params' => ['country' => 'NL', 'percentage' => 21.0],
            ]
        );

        $check = $this->createCheck($logger, [], [new Rate(0, 'NL (standard)', 'NL', '*', 21.0)]);
        $check->execute();
    }

    public function testPlainLoggerReceivesWarningWithoutHtml()
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning')->with(
            $this->logicalNot($this->stringContains('<'))
        );

        $storedRate = new Rate(42, 'NL', 'NL', '*', 19.0);
        $this->createCheck($logger)->checkStoredRate($storedRate, [new Rate(0, 'NL', 'NL', '*', 21.0)]);
    }

    private function createCheck(LoggerInterface $logger, array $storedRates = [], array $onlineRates = []): Check
    {
        $config = $this->createMock(Config::class);
        $config->method('fixAutomatically')->willReturn(false);

        $storedRatesProvider = $this->createMock(StoredRates::class);
        $storedRatesProvider->method('getRates')->willReturn($storedRates);

        $onlineRatesProvider = $this->createMock(OnlineRates::class);
        $onlineRatesProvider->method('getRates')->willReturn($onlineRates);

        $vatModel = $this->createMock(VatModel::class);
        $vatModel->method('isCountryInEU')->willReturn(true);

        $url = $this->createMock(UrlInterface::class);
        $url->method('getUrl')->willReturnCallback(function (string $route) {
            return 'https://example.com/' . $route . '/';
        });

        return new Check($config, $logger, $onlineRatesProvider, $storedRatesProvider, new Comparer(), $vatModel, $url);
    }
}

interface ActionLoggerTestDouble extends LoggerInterface, ActionLoggerInterface
{
}

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
    /**
     * @var array<string, string[]>
     */
    private $messages = [];

    /**
     * @var array
     */
    private $actions = [];

    protected function setUp(): void
    {
        $this->messages = ['info' => [], 'success' => [], 'warning' => [], 'error' => []];
        $this->actions = [];
    }

    public function testWarnsAboutMissingOnlineRatesOnly()
    {
        $this->createCheck([$this->createRate('US', 0.0)], [])->execute();

        $this->assertSame(['No online rates found'], $this->messages['warning']);
    }

    public function testWarnsAboutMissingStoredRatesOnly()
    {
        $this->createCheck([], [$this->createRate('NL', 0.0)])->execute();

        $this->assertSame(['No stored rates found'], $this->messages['warning']);
    }

    public function testWarnsAboutBothMissingStoredAndOnlineRates()
    {
        $this->createCheck([], [])->execute();

        $this->assertSame(['No stored rates found', 'No online rates found'], $this->messages['warning']);
    }

    public function testNoWarningsWhenStoredAndOnlineRatesMatch()
    {
        $rate = $this->createRate('NL', 21.0);
        $this->createCheck([$rate], [$this->createRate('NL', 21.0)], true)->execute();

        $this->assertSame([], $this->messages['warning']);
    }

    public function testVerbosityLogsFoundStoredAndOnlineRates()
    {
        $check = $this->createCheck([$this->createRate('NL', 21.0)], [$this->createRate('NL', 21.0)], true);
        $check->setVerbosity(1);
        $check->execute();

        $this->assertContains('Found stored rate: NL = 21', $this->messages['info']);
        $this->assertContains('Found online rate: NL = 21', $this->messages['info']);
    }

    public function testWarnsAboutIncorrectStoredRateWhenOnlineRatesAreMissing()
    {
        $this->createCheck([$this->createRate('NL', 21.0)], [], true)->execute();

        $this->assertSame('No online rates found', $this->messages['warning'][0]);
        $this->assertCount(2, $this->messages['warning']);
        $this->assertStringContainsString('Existing rate "NL" (21%) seems incorrect.', $this->messages['warning'][1]);
    }

    public function testIncorrectStoredRateWarningIsPlainTextWithFixAction()
    {
        $storedRate = new Rate(42, '<img src=x onerror=alert(1)>', 'NL', '*', 20.0);
        $this->createCheck([$storedRate], [$this->createRate('NL', 21.0)], true)->execute();

        $this->assertCount(2, $this->messages['warning']);
        foreach ($this->messages['warning'] as $message) {
            $this->assertStringNotContainsString('<a ', $message);
            $this->assertStringNotContainsString('href', $message);
        }
        $this->assertSame('https://example.com/', $this->actions['warning'][0]['url']);
        $this->assertSame(['id' => 42], $this->actions['warning'][0]['params']);
    }

    public function testNewOnlineRateWarningIsPlainTextWithAddAction()
    {
        $onlineRates = [$this->createRate('NL', 21.0), $this->createRate('BE', 21.0)];
        $this->createCheck([$this->createRate('NL', 21.0)], $onlineRates, true)->execute();

        $this->assertCount(1, $this->messages['warning']);
        $this->assertStringNotContainsString('<a ', $this->messages['warning'][0]);
        $this->assertSame(['country' => 'BE', 'percentage' => 21.0], $this->actions['warning'][0]['params']);
    }

    private function createRate(string $countryId, float $percentage): Rate
    {
        return new Rate(1, $countryId, $countryId, '*', $percentage);
    }

    /**
     * @param Rate[] $storedRates
     * @param Rate[] $onlineRates
     * @param bool $isCountryInEu
     * @return Check
     */
    private function createCheck(array $storedRates, array $onlineRates, bool $isCountryInEu = false): Check
    {
        $logger = $this->createMock(LoggerInterface::class);
        foreach (array_keys($this->messages) as $type) {
            $logger->method($type)->willReturnCallback(function (string $msg, array $action = []) use ($type) {
                $this->messages[$type][] = $msg;
                $this->actions[$type][] = $action;
            });
        }

        $storedRatesProvider = $this->createStub(StoredRates::class);
        $storedRatesProvider->method('getRates')->willReturn($storedRates);

        $onlineRatesProvider = $this->createStub(OnlineRates::class);
        $onlineRatesProvider->method('getRates')->willReturn($onlineRates);

        $vatModel = $this->createStub(VatModel::class);
        $vatModel->method('isCountryInEU')->willReturn($isCountryInEu);

        $config = $this->createStub(Config::class);
        $config->method('fixAutomatically')->willReturn(false);

        $url = $this->createStub(UrlInterface::class);
        $url->method('getUrl')->willReturn('https://example.com/');

        return new Check(
            $config,
            $logger,
            $onlineRatesProvider,
            $storedRatesProvider,
            new Comparer(),
            $vatModel,
            $url,
            0
        );
    }
}

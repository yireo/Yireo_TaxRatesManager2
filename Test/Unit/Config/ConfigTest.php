<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Test\Unit\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yireo\TaxRatesManager2\Config\Config;
use Yireo\TaxRatesManager2\Util\CommandLine;

class ConfigTest extends TestCase
{
    /**
     * @dataProvider feedSourceProvider
     */
    #[DataProvider('feedSourceProvider')]
    public function testGetFeedUrlFromFeedSource(?string $feedSource, string $expectedFeed)
    {
        $config = $this->createConfig(['taxratesmanager/settings/feed_source' => $feedSource]);
        $this->assertSame(Config::PREFIX . $expectedFeed, $config->getFeedUrl());
    }

    public static function feedSourceProvider(): array
    {
        return [
            ['tax_rates_nl.csv', 'tax_rates_nl.csv'],
            ['tax_rates_us.csv', 'tax_rates_us.csv'],
            [null, 'tax_rates_eu.csv'],
            ['', 'tax_rates_eu.csv'],
            ['../../other-repo/master/evil.csv', 'tax_rates_eu.csv'],
            ['tax_rates_eu.csv?x=1', 'tax_rates_eu.csv'],
            ["tax_rates_nl.csv\n", 'tax_rates_eu.csv'],
            ['tax_rates_nl.json', 'tax_rates_eu.csv'],
        ];
    }

    public function testGetFeedUrlPrefersAlternativeFeedSource()
    {
        $config = $this->createConfig([
            'taxratesmanager/settings/alternative_feed_source' => 'https://example.com/feed.csv',
            'taxratesmanager/settings/feed_source' => 'tax_rates_nl.csv',
        ]);

        $this->assertSame('https://example.com/feed.csv', $config->getFeedUrl());
    }

    private function createConfig(array $values): Config
    {
        $scopeConfig = $this->createStub(ScopeConfigInterface::class);
        $scopeConfig->method('getValue')->willReturnCallback(fn (string $path) => $values[$path] ?? null);

        return new Config($scopeConfig, $this->createStub(CommandLine::class));
    }
}

<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Test\Unit\Util;

use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yireo\TaxRatesManager2\Util\UrlValidator;

class UrlValidatorTest extends TestCase
{
    /**
     * @dataProvider validUrlProvider
     */
    #[DataProvider('validUrlProvider')]
    public function testValidUrlIsAccepted(string $url)
    {
        (new UrlValidator())->validate($url);
        $this->addToAssertionCount(1);
    }

    public static function validUrlProvider(): array
    {
        return [
            ['https://8.8.8.8/tax_rates.csv'],
            ['HTTPS://1.1.1.1/feed.csv'],
            ['https://[2606:4700:4700::1111]/feed.csv'],
        ];
    }

    /**
     * @dataProvider invalidUrlProvider
     */
    #[DataProvider('invalidUrlProvider')]
    public function testInvalidUrlIsRejected(string $url)
    {
        $this->expectException(LocalizedException::class);
        (new UrlValidator())->validate($url);
    }

    public static function invalidUrlProvider(): array
    {
        return [
            ['http://8.8.8.8/feed.csv'],
            ['ftp://8.8.8.8/feed.csv'],
            ['file:///etc/passwd'],
            ['https://'],
            ['not a url'],
            ['https://127.0.0.1/feed.csv'],
            ['https://localhost/feed.csv'],
            ['https://10.0.0.1/feed.csv'],
            ['https://172.16.0.1/feed.csv'],
            ['https://192.168.1.1/feed.csv'],
            ['https://169.254.169.254/latest/meta-data/'],
            ['https://100.64.0.1/feed.csv'],
            ['https://0.0.0.0/feed.csv'],
            ['https://[::1]/feed.csv'],
            ['https://[fc00::1]/feed.csv'],
            ['https://[fe80::1]/feed.csv'],
            ['https://[::ffff:127.0.0.1]/feed.csv'],
            ['https://user:pass@8.8.8.8/feed.csv'],
            ['https://does-not-exist.invalid/feed.csv'],
        ];
    }

    /**
     * @dataProvider ipProvider
     */
    #[DataProvider('ipProvider')]
    public function testIsPublicIp(string $ip, bool $expected)
    {
        $this->assertSame($expected, (new UrlValidator())->isPublicIp($ip));
    }

    public static function ipProvider(): array
    {
        return [
            ['8.8.8.8', true],
            ['2606:4700:4700::1111', true],
            ['127.0.0.1', false],
            ['10.1.2.3', false],
            ['192.168.0.10', false],
            ['169.254.169.254', false],
            ['::1', false],
            ['fd12:3456::1', false],
            ['not-an-ip', false],
        ];
    }
}

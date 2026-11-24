<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Test\Unit\Rate;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yireo\TaxRatesManager2\Rate\Rate;
use Yireo\TaxRatesManager2\Rate\RateValidator;

class RateValidatorTest extends TestCase
{
    /**
     * @dataProvider validRateProvider
     */
    #[DataProvider('validRateProvider')]
    public function testValidRate(string $code, string $countryId, string $postcode, float $percentage)
    {
        $this->assertTrue((new RateValidator())->isValid(new Rate(0, $code, $countryId, $postcode, $percentage)));
    }

    public static function validRateProvider(): array
    {
        return [
            ['Netherlands (standard)', 'NL', '', 21.0],
            ['Austria (reduced: food / books / pharma / hotels)', 'AT', '', 10.0],
            ['Ireland (reduced: fuel, electricity; building & services)', 'IE', '*', 13.5],
            ['Germany (zero: Intra-comm. and internat. transp. (excl road & rail))', 'DE', '', 0.0],
            ['US-CA', 'US', '*', 7.25],
            ['Österreich (Standard)', 'AT', '1010-1090', 20.0],
            ['Max', 'NL', '1234*', 100.0],
        ];
    }

    /**
     * @dataProvider invalidRateProvider
     */
    #[DataProvider('invalidRateProvider')]
    public function testInvalidRate(string $code, string $countryId, string $postcode, float $percentage)
    {
        $this->assertFalse((new RateValidator())->isValid(new Rate(0, $code, $countryId, $postcode, $percentage)));
    }

    public static function invalidRateProvider(): array
    {
        return [
            ['', 'NL', '', 21.0],
            [str_repeat('a', 256), 'NL', '', 21.0],
            ['<img src=x onerror=alert(1)>', 'NL', '', 21.0],
            ['"quoted"', 'NL', '', 21.0],
            ["Line\nbreak", 'NL', '', 21.0],
            ['Netherlands', 'nl', '', 21.0],
            ['Netherlands', 'NLD', '', 21.0],
            ['Netherlands', 'x"><img src=x>', '', 21.0],
            ['Netherlands', '', '', 21.0],
            ['Netherlands', 'NL', '<script>', 21.0],
            ['Netherlands', 'NL', str_repeat('1', 22), 21.0],
            ['Netherlands', 'NL', '', -1.0],
            ['Netherlands', 'NL', '', 100.01],
            ['Netherlands', 'NL', '', NAN],
            ['Netherlands', 'NL', '', INF],
        ];
    }

    /**
     * @dataProvider countryIdProvider
     */
    #[DataProvider('countryIdProvider')]
    public function testIsValidCountryId(string $countryId, bool $expected)
    {
        $this->assertSame($expected, (new RateValidator())->isValidCountryId($countryId));
    }

    public static function countryIdProvider(): array
    {
        return [
            ['NL', true],
            ['US', true],
            ['nl', false],
            ['N', false],
            ['NLD', false],
            ["NL\n", false],
            ['', false],
        ];
    }
}

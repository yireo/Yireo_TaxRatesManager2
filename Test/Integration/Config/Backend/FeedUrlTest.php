<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Test\Integration\Config\Backend;

use Magento\Config\Model\Config\Structure;
use Magento\Framework\Exception\ValidatorException;
use PHPUnit\Framework\Attributes\DataProvider;
use Yireo\TaxRatesManager2\Config\Backend\FeedUrl;
use Yireo\TaxRatesManager2\Test\Integration\TestCase;

class FeedUrlTest extends TestCase
{
    private const CONFIG_PATH = 'taxratesmanager/settings/alternative_feed_source';

    /**
     * @dataProvider validValueProvider
     */
    #[DataProvider('validValueProvider')]
    public function testBeforeSaveAcceptsValidValue(string $value, string $expectedValue)
    {
        $backendModel = $this->createBackendModel($value);
        $backendModel->beforeSave();

        $this->assertSame($expectedValue, $backendModel->getValue());
    }

    public static function validValueProvider(): array
    {
        return [
            ['', ''],
            ['   ', ''],
            [' https://8.8.8.8/tax_rates.csv ', 'https://8.8.8.8/tax_rates.csv'],
        ];
    }

    /**
     * @dataProvider invalidValueProvider
     */
    #[DataProvider('invalidValueProvider')]
    public function testBeforeSaveRejectsInvalidValue(string $value)
    {
        $this->expectException(ValidatorException::class);
        $this->createBackendModel($value)->beforeSave();
    }

    public static function invalidValueProvider(): array
    {
        return [
            ['http://8.8.8.8/tax_rates.csv'],
            ['file:///etc/passwd'],
            ['https://127.0.0.1/tax_rates.csv'],
            ['https://169.254.169.254/latest/meta-data/'],
            ['https://localhost/tax_rates.csv'],
        ];
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testAdminFieldUsesValidatingBackendModel()
    {
        $field = $this->getObjectManager()->get(Structure::class)->getElementByConfigPath(self::CONFIG_PATH);

        $this->assertTrue($field->hasBackendModel());
        $this->assertInstanceOf(FeedUrl::class, $field->getBackendModel());
    }

    private function createBackendModel(string $value): FeedUrl
    {
        $backendModel = $this->getObjectManager()->create(FeedUrl::class);
        $backendModel->setPath(self::CONFIG_PATH);
        $backendModel->setScope('default');
        $backendModel->setScopeId(0);
        $backendModel->setValue($value);

        return $backendModel;
    }
}

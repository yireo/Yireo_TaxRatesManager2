<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Test\Integration\Config\Backend;

use Magento\Config\Model\Config\Structure;
use Magento\Framework\Exception\ValidatorException;
use PHPUnit\Framework\Attributes\DataProvider;
use Yireo\TaxRatesManager2\Config\Backend\Email;
use Yireo\TaxRatesManager2\Test\Integration\TestCase;

class EmailTest extends TestCase
{
    private const CONFIG_PATH = 'taxratesmanager/settings/email';

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
            ['  ', ''],
            [' info@example.com ', 'info@example.com'],
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
            ['not-an-email'],
            ["info@example.com\nBcc: evil@example.com"],
            ['<script>@example.com'],
        ];
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testAdminFieldUsesValidatingBackendModel()
    {
        $field = $this->getObjectManager()->get(Structure::class)->getElementByConfigPath(self::CONFIG_PATH);

        $this->assertTrue($field->hasBackendModel());
        $this->assertInstanceOf(Email::class, $field->getBackendModel());
    }

    private function createBackendModel(string $value): Email
    {
        $backendModel = $this->getObjectManager()->create(Email::class);
        $backendModel->setPath(self::CONFIG_PATH);
        $backendModel->setScope('default');
        $backendModel->setScopeId(0);
        $backendModel->setValue($value);

        return $backendModel;
    }
}

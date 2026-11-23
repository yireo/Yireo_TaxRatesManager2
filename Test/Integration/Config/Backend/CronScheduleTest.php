<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Test\Integration\Config\Backend;

use Magento\Config\Model\Config;
use Magento\Config\Model\ResourceModel\Config\Data\Collection as ConfigCollection;
use Magento\Framework\Exception\ValidatorException;
use Yireo\TaxRatesManager2\Config\Backend\CronSchedule;
use Yireo\TaxRatesManager2\Test\Integration\TestCase;

class CronScheduleTest extends TestCase
{
    private const CONFIG_PATH = 'taxratesmanager/settings/cron_schedule';

    /**
     * @dataProvider validValueProvider
     */
    public function testBeforeSaveAcceptsValidValue(string $value, string $expectedValue)
    {
        $backendModel = $this->createBackendModel($value);
        $backendModel->beforeSave();

        $this->assertSame($expectedValue, $backendModel->getValue());
    }

    public static function validValueProvider(): array
    {
        return [
            ['0 3 * * *', '0 3 * * *'],
            ['*/5 * * * *', '*/5 * * * *'],
            ['0 */6 * * mon-fri', '0 */6 * * mon-fri'],
            ['15 2,14 1 jan *', '15 2,14 1 jan *'],
            ['  0 3 * * *  ', '0 3 * * *'],
            ['', ''],
            ['   ', ''],
        ];
    }

    /**
     * @dataProvider invalidValueProvider
     */
    public function testBeforeSaveRejectsInvalidValue(string $value)
    {
        $this->expectException(ValidatorException::class);
        $this->createBackendModel($value)->beforeSave();
    }

    public static function invalidValueProvider(): array
    {
        return [
            ['daily'],
            ['0 3 * *'],
            ['0 3 * * * * *'],
            ['a/b * * * *'],
            ['*/0 * * * *'],
            ['0 3 * * foo'],
            ['1-2-3 * * * *'],
        ];
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppArea adminhtml
     */
    public function testSaveRejectsInvalidValue()
    {
        $this->expectException(ValidatorException::class);
        $this->saveConfig('every day');
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppArea adminhtml
     */
    public function testSaveStoresValidValue()
    {
        $this->saveConfig('0 */6 * * *');

        $collection = $this->getObjectManager()->create(ConfigCollection::class);
        $collection->addFieldToFilter('path', self::CONFIG_PATH);
        $collection->addFieldToFilter('scope', 'default');
        $this->assertSame('0 */6 * * *', $collection->getFirstItem()->getValue());
    }

    private function saveConfig(string $value): void
    {
        $config = $this->getObjectManager()->create(Config::class);
        $config->setDataByPath(self::CONFIG_PATH, $value);
        $config->save();
    }

    private function createBackendModel(string $value): CronSchedule
    {
        $backendModel = $this->getObjectManager()->create(CronSchedule::class);
        $backendModel->setPath(self::CONFIG_PATH);
        $backendModel->setScope('default');
        $backendModel->setScopeId(0);
        $backendModel->setValue($value);

        return $backendModel;
    }
}

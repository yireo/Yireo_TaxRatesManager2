<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Test\Integration\Cron;

use Magento\Config\Model\Config\Structure;
use Magento\Config\Model\Config\Structure\Element\Field;
use Magento\Cron\Model\ConfigInterface as CronConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Yireo\TaxRatesManager2\Config\Backend\CronSchedule;
use Yireo\TaxRatesManager2\Cron\CheckRunner;
use Yireo\TaxRatesManager2\Test\Integration\TestCase;

class CronScheduleConfigTest extends TestCase
{
    private const CONFIG_PATH = 'taxratesmanager/settings/cron_schedule';

    public function testCronJobReadsScheduleFromConfigWithDailyFallback()
    {
        $jobs = $this->getObjectManager()->get(CronConfig::class)->getJobs();
        $this->assertArrayHasKey('yireo_taxratesmanager2_check', $jobs['default']);

        $job = $jobs['default']['yireo_taxratesmanager2_check'];
        $this->assertSame(CheckRunner::class, $job['instance']);
        $this->assertSame('execute', $job['method']);
        $this->assertSame('0 3 * * *', $job['schedule']);
        $this->assertSame(self::CONFIG_PATH, $job['config_path']);
    }

    public function testDefaultScheduleIsDaily()
    {
        $scopeConfig = $this->getObjectManager()->get(ScopeConfigInterface::class);
        $this->assertSame('0 3 * * *', $scopeConfig->getValue(self::CONFIG_PATH));
    }

    /**
     * @magentoAppArea adminhtml
     */
    public function testAdminFieldUsesValidatingBackendModel()
    {
        $structure = $this->getObjectManager()->get(Structure::class);
        $field = $structure->getElementByConfigPath(self::CONFIG_PATH);

        $this->assertInstanceOf(Field::class, $field);
        $this->assertTrue($field->hasBackendModel());
        $this->assertInstanceOf(CronSchedule::class, $field->getBackendModel());
    }
}

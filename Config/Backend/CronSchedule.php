<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Config\Backend;

use Magento\Cron\Model\ScheduleFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\CronException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class CronSchedule extends Value
{
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        private readonly ScheduleFactory $scheduleFactory,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function beforeSave()
    {
        $value = trim((string)$this->getValue(), " \f\n\r\t\v\x00");
        $this->setValue($value);

        if ($value !== '') {
            $this->validate($value);
        }

        return parent::beforeSave();
    }

    private function validate(string $value): void
    {
        try {
            $schedule = $this->scheduleFactory->create();
            $schedule->setCronExpr($value);
            foreach (array_slice($schedule->getCronExprArr(), 0, 5) as $part) {
                $schedule->matchCronExpression($part, 0);
            }
        } catch (CronException $exception) {
            throw new ValidatorException(
                __('Invalid cron schedule "%1": %2', $value, $exception->getMessage())
            );
        }
    }
}

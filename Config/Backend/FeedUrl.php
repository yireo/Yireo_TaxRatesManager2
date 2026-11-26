<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Yireo\TaxRatesManager2\Util\UrlValidator;

class FeedUrl extends Value
{
    /**
     * @var UrlValidator
     */
    private $urlValidator;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        UrlValidator $urlValidator,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->urlValidator = $urlValidator;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function beforeSave()
    {
        $value = trim((string)$this->getValue(), " \f\n\r\t\v\x00");
        $this->setValue($value);

        if ($value !== '') {
            try {
                $this->urlValidator->validate($value);
            } catch (LocalizedException $exception) {
                throw new ValidatorException(__('%1', $exception->getMessage()), $exception);
            }
        }

        return parent::beforeSave();
    }
}

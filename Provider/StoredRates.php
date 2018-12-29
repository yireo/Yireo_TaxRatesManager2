<?php
/**
 * TaxRatesManager2 module for Magento
 *
 * @package     Yireo_TaxRatesManager2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2018 Yireo (https://www.yireo.com/)
 * @license     Open Source License 3
 */

declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Provider;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Api\Data\TaxRateInterface;
use Magento\Tax\Api\Data\TaxRateInterfaceFactory;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Yireo\TaxRatesManager2\Config\Config;
use Yireo\TaxRatesManager2\Rate\Rate;

/**
 * Class StoredRates
 */
class StoredRates
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var TaxRateRepositoryInterface
     */
    private $taxRateRepository;
    
    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var TaxRateInterfaceFactory
     */
    private $taxRateFactory;

    /**
     * StoredRates constructor.
     * @param Config $config
     * @param TaxRateRepositoryInterface $taxRateRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param TaxRateInterfaceFactory $taxRateFactory
     */
    public function __construct(
        Config $config,
        TaxRateRepositoryInterface $taxRateRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        TaxRateInterfaceFactory $taxRateFactory
    ) {
        $this->config = $config;
        $this->taxRateRepository = $taxRateRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->taxRateFactory = $taxRateFactory;
    }

    /**
     * @param int $id
     * @return TaxRateInterface
     * @throws NoSuchEntityException
     */
    public function getRateById(int $id): TaxRateInterface
    {
        return $this->taxRateRepository->get($id);
    }

    /**
     * @return Rate[]
     * @throws InputException
     */
    public function getRates(): array
    {
        $rates = [];

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria = $searchCriteriaBuilder->create();
        $searchResult = $this->taxRateRepository->getList($searchCriteria);

        /** @var TaxRateInterface $item */
        foreach ($searchResult->getItems() as $item) {
            $rates[] = new Rate(
                (int)$item->getId(),
                (string)$item->getCode(),
                (string)$item->getTaxCountryId(),
                (float)$item->getRate()
            );
        }

        return $rates;
    }

    /**
     * Save a new or existing rate
     *
     * @param Rate $rate
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function saveRate(Rate $rate)
    {
        $model = $this->getTaxRateModelById($rate->getId());

        if (!$rate->getId() > 0 || $this->config->updateNameFromExistingItems()) {
            $model->setCode($rate->getCode());
        }

        $model->setTaxCountryId($rate->getCountryId());
        $model->setRate($rate->getPercentage());
        $model->setTaxPostcode('*');
        $this->taxRateRepository->save($model);
    }

    /**
     * Delete a rate
     *
     * @param Rate $rate
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function deleteRate(Rate $rate)
    {
        $model = $this->getTaxRateModelById($rate->getId());
        $this->taxRateRepository->delete($model);
    }

    /**
     * @param int $id
     * @return TaxRateInterface
     * @throws NoSuchEntityException
     */
    private function getTaxRateModelById(int $id = 0): TaxRateInterface
    {
        if ($id > 0) {
            return $this->taxRateRepository->get($id);
        }

        return $this->taxRateFactory->create();
    }
}

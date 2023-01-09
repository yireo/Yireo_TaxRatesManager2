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

namespace Yireo\TaxRatesManager2\Rate;

/**
 * Class Rate
 */
class Rate
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $countryId;

    /**
     * @var string
     */
    private $postcode;

    /**
     * @var float
     */
    private $percentage;

    /**
     * Rate constructor.
     * @param int $id
     * @param string $code
     * @param string $countryId
     * @param float $percentage
     */
    public function __construct(
        int $id,
        string $code,
        string $countryId,
        string $postcode,
        float $percentage
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->countryId = $countryId;
        $this->postcode = $postcode;
        $this->percentage = $percentage;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getCountryId(): string
    {
        return $this->countryId;
    }

    /**
     * @return string
     */
    public function getPostcode(): string
    {
        return $this->postcode;
    }

    /**
     * @param string $postcode
     */
    public function setPostcode(string $postcode): void
    {
        $this->postcode = $postcode;
    }

    /**
     * @return float
     */
    public function getPercentage(): float
    {
        return $this->percentage;
    }

    /**
     * @param float $percentage
     */
    public function setPercentage(float $percentage)
    {
        $this->percentage = $percentage;
    }
}

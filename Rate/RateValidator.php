<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Rate;

class RateValidator
{
    /**
     * @param Rate $rate
     * @return bool
     */
    public function isValid(Rate $rate): bool
    {
        return $this->isValidCode($rate->getCode())
            && $this->isValidCountryId($rate->getCountryId())
            && $this->isValidPostcode($rate->getPostcode())
            && $this->isValidPercentage($rate->getPercentage());
    }

    /**
     * @param string $countryId
     * @return bool
     */
    public function isValidCountryId(string $countryId): bool
    {
        return preg_match('/^[A-Z]{2}$/D', $countryId) === 1;
    }

    /**
     * @param float $percentage
     * @return bool
     */
    public function isValidPercentage(float $percentage): bool
    {
        return is_finite($percentage) && $percentage >= 0 && $percentage <= 100;
    }

    /**
     * @param string $code
     * @return bool
     */
    private function isValidCode(string $code): bool
    {
        return preg_match('/^[\p{L}\p{N} &(),.\/:;+%_-]{1,255}$/uD', $code) === 1;
    }

    /**
     * @param string $postcode
     * @return bool
     */
    private function isValidPostcode(string $postcode): bool
    {
        return preg_match('/^[A-Za-z0-9* -]{0,21}$/D', $postcode) === 1;
    }
}

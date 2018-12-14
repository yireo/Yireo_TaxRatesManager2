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

namespace Yireo\TaxRatesManager2\Util;

/**
 * Class Comparer
 * @package Yireo\TaxRatesManager2\Util
 */
class Comparer
{
    /**
     * @param float $target
     * @param float $option1
     * @param float $option2
     * @return float
     */
    public function getSmallestDifference(float $target, float $option1, float $option2): float
    {
        if (abs($target - $option1) < abs($target - $option2)) {
            return $option1;
        }

        return $option2;
    }
}

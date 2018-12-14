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
 * Class CommandLine
 */
class CommandLine
{
    /**
     * @return bool
     */
    public function isCli(): bool
    {
        return (!isset($_SERVER['SERVER_SOFTWARE']) && (php_sapi_name() == 'cli'));
    }
}

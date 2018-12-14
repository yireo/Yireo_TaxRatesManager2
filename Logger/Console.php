<?php
/**
 * Yireo TaxRatesManager2 extension for Magento 2
 *
 * @package     Yireo_TaxRatesManager2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2018 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Logger;

use Yireo\TaxRatesManager2\Api\LoggerInterface;

/**
 * Class Console
 */
class Console implements LoggerInterface
{
    /**
     * @param string $msg
     */
    public function info(string $msg)
    {
        echo "INFO: ".$msg."\n";
    }

    /**
     * @param string $msg
     */
    public function success(string $msg)
    {
        echo "SUCCESS: ".$msg."\n";
    }

    /**
     * @param string $msg
     */
    public function warning(string $msg)
    {
        echo "WARNING: ".$msg."\n";
    }

    /**
     * @param string $msg
     */
    public function error(string $msg)
    {
        echo "ERROR: ".$msg."\n";
    }
}

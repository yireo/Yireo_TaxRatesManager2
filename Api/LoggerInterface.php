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

namespace Yireo\TaxRatesManager2\Api;

/**
 * Interface LoggerInterface
 * @package Yireo\TaxRatesManager2\Api
 */
interface LoggerInterface
{
    /**
     * @param string $msg
     */
    public function info(string $msg);

    /**
     * @param string $msg
     */
    public function success(string $msg);

    /**
     * @param string $msg Plain text, never HTML
     * @param array{url?: string, label?: string, params?: array<string, scalar>} $action Optional POST action
     */
    public function warning(string $msg, array $action = []);

    /**
     * @param string $msg
     */
    public function error(string $msg);
}

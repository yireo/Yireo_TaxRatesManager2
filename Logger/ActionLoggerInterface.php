<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Logger;

interface ActionLoggerInterface
{
    /**
     * @param string $msg Plain text, never HTML
     * @param array{url?: string, label?: string, params?: array<string, scalar>} $action Optional POST action
     */
    public function warningWithAction(string $msg, array $action = []);
}

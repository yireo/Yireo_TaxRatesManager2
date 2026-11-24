<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Util;

use Magento\Framework\Exception\LocalizedException;
use Throwable;

class UrlValidator
{
    /**
     * @param string $url
     * @return void
     * @throws LocalizedException
     */
    public function validate(string $url): void
    {
        $parts = parse_url($url);
        if (!is_array($parts)
            || strtolower((string)($parts['scheme'] ?? '')) !== 'https'
            || empty($parts['host'])) {
            throw new LocalizedException(__('The feed URL "%1" is not a valid https:// URL', $url));
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            throw new LocalizedException(__('The feed URL "%1" must not contain credentials', $url));
        }

        $host = trim($parts['host'], '[]');
        $ips = $this->resolve($host);
        if (empty($ips)) {
            throw new LocalizedException(__('The feed URL host "%1" could not be resolved', $host));
        }

        foreach ($ips as $ip) {
            if (!$this->isPublicIp($ip)) {
                throw new LocalizedException(
                    __('The feed URL host "%1" resolves to a private or reserved IP address', $host)
                );
            }
        }
    }

    /**
     * @param string $ip
     * @return bool
     */
    public function isPublicIp(string $ip): bool
    {
        $flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE | FILTER_FLAG_GLOBAL_RANGE;
        return filter_var($ip, FILTER_VALIDATE_IP, $flags) !== false;
    }

    /**
     * @param string $host
     * @return string[]
     */
    private function resolve(string $host): array
    {
        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return [$host];
        }

        $ips = gethostbynamel($host) ?: [];

        try {
            foreach (dns_get_record($host, DNS_AAAA) ?: [] as $record) {
                if (!empty($record['ipv6'])) {
                    $ips[] = (string)$record['ipv6'];
                }
            }
        } catch (Throwable $exception) {
            return $ips;
        }

        return $ips;
    }
}

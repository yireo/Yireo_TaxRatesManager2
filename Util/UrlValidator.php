<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Util;

use Magento\Framework\Exception\LocalizedException;
use Throwable;

class UrlValidator
{
    /**
     * @var string[]
     */
    private $nonPublicRanges = [
        '0.0.0.0/8',
        '100.64.0.0/10',
        '192.0.0.0/24',
        '192.88.99.0/24',
        '198.18.0.0/15',
        '::/128',
        '::1/128',
        '::ffff:0:0/96',
        '64:ff9b::/96',
        '100::/64',
        '2001::/23',
        '2001:db8::/32',
        '2002::/16',
        'fc00::/7',
        'fe80::/10',
    ];

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
        $flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
        if (filter_var($ip, FILTER_VALIDATE_IP, $flags) === false) {
            return false;
        }

        foreach ($this->nonPublicRanges as $range) {
            if ($this->isInRange($ip, $range)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $ip
     * @param string $range
     * @return bool
     */
    private function isInRange(string $ip, string $range): bool
    {
        [$subnet, $bits] = explode('/', $range);
        $ipBinary = inet_pton($ip);
        $subnetBinary = inet_pton($subnet);
        if ($ipBinary === false || $subnetBinary === false || strlen($ipBinary) !== strlen($subnetBinary)) {
            return false;
        }

        $bits = (int)$bits;
        $bytes = intdiv($bits, 8);
        if (substr($ipBinary, 0, $bytes) !== substr($subnetBinary, 0, $bytes)) {
            return false;
        }

        $remainder = $bits % 8;
        if ($remainder === 0) {
            return true;
        }

        $mask = (0xFF << (8 - $remainder)) & 0xFF;
        return (ord($ipBinary[$bytes]) & $mask) === (ord($subnetBinary[$bytes]) & $mask);
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

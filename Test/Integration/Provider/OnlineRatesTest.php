<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Test\Integration\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use Magento\Framework\Exception\LocalizedException;
use Yireo\TaxRatesManager2\Provider\OnlineRates;
use Yireo\TaxRatesManager2\Test\Integration\TestCase;

class OnlineRatesTest extends TestCase
{
    private const HEADER = 'Code,Country,State,Zip/Post Code,Rate,Zip/Post is Range,Range From,Range To,default';

    public function testInvalidRowsAreSkipped()
    {
        $csv = implode("\n", [
            self::HEADER,
            '"Netherlands (standard)",NL,*,,21.0000,,,,',
            '"<img src=x onerror=alert(1)>",NL,*,,21.0000,,,,',
            '"Evil country","x""><img src=x>",*,,21.0000,,,,',
            '"Too much",NL,*,,250.0000,,,,',
            '"Belgium (standard)",BE,*,,21.0000,,,,',
        ]);

        $rates = $this->createOnlineRates([new Response(200, [], $csv)])->getRates();

        $codes = array_map(fn ($rate) => $rate->getCode(), $rates);
        $this->assertSame(['Netherlands (standard)', 'Belgium (standard)'], $codes);
    }

    public function testUnexpectedHeaderDoesNotLeakResponseContent()
    {
        $secret = 'internal-secret-value';
        try {
            $this->createOnlineRates([new Response(200, [], $secret . "\nfoo")])->getRates();
            $this->fail('Expected an exception for an invalid CSV header');
        } catch (InvalidArgumentException $exception) {
            $this->assertStringNotContainsString($secret, $exception->getMessage());
        }
    }

    public function testRedirectToPrivateAddressIsRejected()
    {
        $this->expectException(LocalizedException::class);
        $responses = [new Response(302, ['Location' => 'https://169.254.169.254/latest/meta-data/'])];
        $this->createOnlineRates($responses)->getRates();
    }

    public function testFailedRequestIsWrappedInLocalizedException()
    {
        $this->expectException(LocalizedException::class);
        $this->createOnlineRates([new Response(500)])->getRates();
    }

    /**
     * @magentoConfigFixture taxratesmanager/settings/alternative_feed_source http://8.8.8.8/feed.csv
     */
    public function testInsecureAlternativeFeedIsRejectedBeforeFetching()
    {
        $handler = new MockHandler([new Response(200, [], self::HEADER)]);
        $this->expectException(LocalizedException::class);

        try {
            $this->createOnlineRates([], $handler)->getRates();
        } finally {
            $this->assertSame(1, $handler->count(), 'No request should have been sent');
        }
    }

    private function createOnlineRates(array $responses, ?MockHandler $handler = null): OnlineRates
    {
        $handler = $handler ?? new MockHandler($responses);
        $client = new Client(['handler' => HandlerStack::create($handler)]);

        return $this->getObjectManager()->create(OnlineRates::class, ['client' => $client]);
    }
}

<?php namespace Service;

use PHPUnit\Framework\TestCase;
use App\Service\CurrencyService;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Class CurrencyServiceTest
 *
 * Unit tests for the CurrencyService class.
 */
class CurrencyServiceTest extends TestCase {

    /**
     * Test that the getExchangeRates method returns the correct data structure.
     */
    public function testGetExchangeRates() {

        // Create a mock HTTP client
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        // Sample API response data
        $mockResponseData = [
            'rates' => [
                ['mid' => 4.1234]
            ]
        ];

        // Configure the mock response to return the sample data as an array
        $mockResponse->method('toArray')->willReturn($mockResponseData);

        // Configure the mock HTTP client to return the mock response
        $mockHttpClient->method('request')->willReturn($mockResponse);

        // Instantiate the CurrencyService with the mock HTTP client
        $currencyService = new CurrencyService($mockHttpClient);

        // Call the getExchangeRates method
        $rates = $currencyService->getExchangeRates('2024-08-06');

        // Assert the data structure and values
        $this->assertIsArray($rates);
        $this->assertCount(5, $rates);

        foreach ($rates as $rate) {

            $this->assertArrayHasKey('code', $rate);
            $this->assertArrayHasKey('name', $rate);
            $this->assertArrayHasKey('nbpRate', $rate);
            $this->assertArrayHasKey('buyRate', $rate);
            $this->assertArrayHasKey('sellRate', $rate);

            // Additional assertions for specific currency rates
            $this->assertEquals(4.1234, $rate['nbpRate']);

            if (in_array($rate['code'], ['EUR', 'USD'])) {
                $this->assertEqualsWithDelta(4.0734, $rate['buyRate'], 0.0001);
                $this->assertEqualsWithDelta(4.1934, $rate['sellRate'], 0.0001);
            } else {
                $this->assertNull($rate['buyRate']);
                $this->assertEqualsWithDelta(4.2734, $rate['sellRate'], 0.0001);
            }

        }

    }

}
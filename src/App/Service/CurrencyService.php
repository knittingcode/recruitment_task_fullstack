<?php namespace App\Service;

use App\Constants\Currency;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Class CurrencyService
 *
 * A service class that interacts with the NBP API to fetch exchange rates
 * for a list of predefined currencies and calculates buy and sell rates
 * based on the average rate provided by the API.
 */
class CurrencyService {

    /**
     * @var HttpClientInterface The HTTP client used to perform requests to the NBP API.
     */
    private $client;

    /**
     * @var array List of currency codes supported by the service.
     */
    private $currencies = [
        Currency::EUR,
        Currency::USD,
        Currency::CZK,
        Currency::IDR,
        Currency::BRL
    ];

    /**
     * CurrencyService constructor.
     *
     * Initializes the CurrencyService with a given HTTP client.
     *
     * @param HttpClientInterface $client An instance of HttpClientInterface used to perform HTTP requests.
     */
    public function __construct(HttpClientInterface $client) {
        $this->client = $client;
    }

    /**
     * Fetch exchange rates for the specified date.
     *
     * This method retrieves exchange rates for a list of predefined currencies from the NBP API
     * for the specified date and calculates the buy and sell rates based on the average rate.
     *
     * @param string $date The date for which to fetch exchange rates in 'Y-m-d' format.
     * @return array An array of exchange rates with currency codes, names, and calculated buy and sell rates.
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface If there is an issue with the network or HTTP transport.
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface If the HTTP client returns a 4xx response.
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface If the HTTP client returns a 5xx response.
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface If the HTTP client encounters a redirection.
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface If the response cannot be decoded.
     */
    public function getExchangeRates(string $date): array {

        $rates = [];

        foreach ($this->currencies as $currency) {

            $response = $this->client->request(
                'GET',
                "https://api.nbp.pl/api/exchangerates/rates/A/$currency/$date/?format=json"
            );

            $data = $response->toArray();

            $averageRate = $data['rates'][0]['mid'];

            $rates[] = [
                'code' => $currency,
                'name' => Currency::getName($currency),
                'nbpRate' => $averageRate,
                'buyRate' => $this->getBuyRate($currency, $averageRate),
                'sellRate' => $this->getSellRate($currency, $averageRate),
            ];

        }

        return $rates;

    }

    /**
     * Calculate the buy rate for a given currency and average rate.
     *
     * For EUR and USD, the buy rate is calculated as the average rate minus 0.05 PLN.
     * For other currencies, buying is not supported, and the method returns null.
     *
     * @param string $currency The currency code (e.g., 'EUR', 'USD').
     * @param float $averageRate The average exchange rate for the currency.
     * @return float|null The calculated buy rate, or null if buying is not supported for the currency.
     */
    private function getBuyRate(string $currency, float $averageRate): ?float {

        if (in_array($currency, [Currency::EUR, Currency::USD])) {
            return $averageRate - 0.05;
        }

        return null; // Buying is not supported for other currencies

    }

    /**
     * Calculate the sell rate for a given currency and average rate.
     *
     * For EUR and USD, the sell rate is calculated as the average rate plus 0.07 PLN.
     * For other currencies, the sell rate is calculated as the average rate plus 0.15 PLN.
     *
     * @param string $currency The currency code (e.g., 'EUR', 'USD').
     * @param float $averageRate The average exchange rate for the currency.
     * @return float The calculated sell rate.
     */
    private function getSellRate(string $currency, float $averageRate): float {

        if (in_array($currency, [Currency::EUR, Currency::USD])) {
            return $averageRate + 0.07;
        }

        return $averageRate + 0.15;

    }

}
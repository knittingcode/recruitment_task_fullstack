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
	 * If the exchange rate for the specified date is not found (404 error), it will step back one day
	 * and retry, up to a maximum number of days, if specified.
	 *
	 * @param string $date The date for which to fetch exchange rates in 'Y-m-d' format.
	 * @param int|null $maxDaysToCheck Maximum number of days to step back if the rate is not found (null means no stepping back).
	 * @return array An array containing both the actual date used to fetch exchange rates and the rates for each currency.
	 *
	 * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface If there is an issue with the network or HTTP transport.
	 * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface If the HTTP client returns a 4xx response.
	 * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface If the HTTP client returns a 5xx response.
	 * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface If the HTTP client encounters a redirection.
	 * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface If the response cannot be decoded.
	 */
	public function getExchangeRates(string $date, ?int $maxDaysToCheck = null): array {

		$rates = [];
		$currentDate = new \DateTime($date);

		foreach ($this->currencies as $currency) {

			// Determine how many days to step back (if any)
			$daysToCheck = $maxDaysToCheck ?? 1; // If $maxDaysToCheck is null, check only the original date

			// Try fetching the exchange rate for the currency, stepping back a day if a 404 error occurs
			for ($i = 0; $i < $daysToCheck; $i++) {
				try {

					// Format the date for the NBP API request
					$formattedDate = $currentDate->format('Y-m-d');

					// Make the request to NBP API
					$response = $this->client->request('GET', "https://api.nbp.pl/api/exchangerates/rates/a/$currency/$formattedDate/?format=json");

					// Parse the response and if successful, stop the loop for this currency
					$data = $response->toArray();
					$averageRate = $data['rates'][0]['mid'];

					// Add the result to the rates array, including the date
					$rates[] = [
						'code' => $currency,
						'name' => Currency::getName($currency),
						'nbpRate' => $averageRate,
						'buyRate' => $this->getBuyRate($currency, $averageRate),
						'sellRate' => $this->getSellRate($currency, $averageRate),
						'date' => $formattedDate, // Include the date used to get the rate
					];

					// Break the loop once we have successfully retrieved the exchange rate
					break;

				} catch (\Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface $e) {

					// If a 404 error occurs, step back one day and try again
					if ($response->getStatusCode() === 404) $currentDate->modify('-1 day');

					// If the error is not a 404, rethrow the exception
					else throw $e;

				}
			}

			// Reset the date to the original date for the next currency
			$currentDate = new \DateTime($date);

		}

		// Returning data
		return [
			'requestedDate' => $date,	// The original date requested by the user
			'dataFromTheDay' => $currentDate->format('Y-m-d'),	// The final date after adjusting (if needed) to retrieve valid rates
			'rates' => $rates,	// List of exchange rates with actual dates used for fetching the rates
		];

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
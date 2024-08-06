<?php namespace App\Controller;

use App\Service\CurrencyService;
use App\Utils\ValidationHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class ExchangeRatesController
 *
 * Handles requests related to exchange rates and returns JSON responses.
 */
class ExchangeRatesController extends AbstractController {

    /**
     * @var CurrencyService $currencyService Service for handling currency data.
     */
    private $currencyService;

    /**
     * @var ValidationHelper $validationHelper Helper class for data validation.
     */
    private $validationHelper;

    /**
     * ExchangeRatesController constructor.
     *
     * @param CurrencyService $currencyService Service for fetching exchange rates.
     * @param ValidationHelper $validationHelper Helper for validating input data.
     */
    public function __construct(CurrencyService $currencyService, ValidationHelper $validationHelper) {
        $this->currencyService = $currencyService;
        $this->validationHelper = $validationHelper;
    }

    /**
     * Get exchange rates for a given date and compare them with today's rates.
     *
     * @param Request $request The HTTP request object.
     *
     * @return JsonResponse Returns a JSON response with exchange rates for the selected date and today.
     */
    public function getExchangeRates(Request $request): JsonResponse {

        // Get date from the request or set it to today's date
        $date = $request->query->get('date', date('Y-m-d'));

        // Use the validation helper to validate the date format
        if (!$this->validationHelper->validateDate($date)) return new JsonResponse(['error' => 'Invalid date format'], 400);
        try {

            // Get exchange rates for the selected date and today's date
            $rates = $this->currencyService->getExchangeRates($date);
            $todayRates = $this->currencyService->getExchangeRates(date('Y-m-d'));

            return new JsonResponse([
                'selectedDate' => $date,
                'rates' => $rates,
                'todayRates' => $todayRates,
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }

    }

    /**
     * Validate the date format to ensure it matches 'Y-m-d'.
     *
     * @param string $date   The date to validate.
     * @param string $format The expected date format. Default is 'Y-m-d'.
     *
     * @return bool Returns true if the date is valid, false otherwise.
     */
    private function validateDate($date, $format = 'Y-m-d'): bool {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

}
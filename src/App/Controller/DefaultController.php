<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CurrencyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class DefaultController extends AbstractController
{

	/**
	 * @var CurrencyService $currencyService Service for handling currency data.
	 */
	private $currencyService;

	/**
	 * ExchangeRatesController constructor.
	 *
	 * @param CurrencyService $currencyService Service for fetching exchange rates.
	 */
	public function __construct(CurrencyService $currencyService) {
		$this->currencyService = $currencyService;
	}

    public function index(): Response
    {

		$todayRates = $this->currencyService->getExchangeRates(date('Y-m-d'), 7);
        return $this->render(
            'exchange_rates/app-root.html.twig', [
				'exchangeRates' => $todayRates
			]
        );

    }

    public function setupCheck(Request $request): Response
    {
        $responseContent = json_encode([
            'testParam' => $request->get('testParam')
                ? (int) $request->get('testParam')
                : null
        ]);
        return new Response(
            $responseContent,
            Response::HTTP_OK,
            ['Content-type' => 'application/json']
        );
    }


}

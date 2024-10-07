<?php namespace Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ExchangeRatesControllerTest
 *
 * Functional tests for the ExchangeRatesController.
 */
class ExchangeRatesControllerTest extends WebTestCase {

    /**
     * Test getting exchange rates for a valid date.
     */
    public function testGetExchangeRatesValidDate() {

        // Create a client to make requests
        $client = static::createClient();

        // Send a GET request with a valid date
        $client->request('GET', '/api/exchange-rates?date=2024-08-01');

        // Assert that the response is successful
        $this->assertResponseIsSuccessful();

        // Assert that the response has a JSON content type
        $this->assertResponseHeaderSame('content-type', 'application/json');

        // Assert that the response contains expected data
        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);

        $this->assertArrayHasKey('selectedDate', $data);
        $this->assertArrayHasKey('rates', $data);
        $this->assertArrayHasKey('todayRates', $data);

        $this->assertEquals('2024-08-01', $data['selectedDate']);

    }

    /**
     * Test getting exchange rates with an invalid date format.
     */
    public function testGetExchangeRatesInvalidDateFormat() {

        $client = static::createClient();

        // Send a GET request with an invalid date
        $client->request('GET', '/api/exchange-rates?date=invalid-date');

        // Assert that the response has a status code 400
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());

        // Assert that the response contains the error message
        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);

        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Invalid date format', $data['error']);

    }

    /**
     * Test getting exchange rates without providing a date (default to today).
     */
    public function testGetExchangeRatesDefaultToToday() {

        $client = static::createClient();

        // Send a GET request without a date
        $client->request('GET', '/api/exchange-rates');

        // Assert that the response is successful
        $this->assertResponseIsSuccessful();

        // Assert that the response has a JSON content type
        $this->assertResponseHeaderSame('content-type', 'application/json');

        // Assert that the response contains today's date
        $responseContent = $client->getResponse()->getContent();
        $data = json_decode($responseContent, true);

        $this->assertArrayHasKey('selectedDate', $data);
        $this->assertArrayHasKey('rates', $data);
        $this->assertArrayHasKey('todayRates', $data);

        $this->assertEquals(date('Y-m-d'), $data['selectedDate']);

    }

}

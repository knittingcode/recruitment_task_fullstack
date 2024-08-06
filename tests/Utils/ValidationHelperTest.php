<?php namespace Utils;

use PHPUnit\Framework\TestCase;
use App\Utils\ValidationHelper;

/**
 * Class ValidationHelperTest
 *
 * Unit tests for the ValidationHelper class.
 */
class ValidationHelperTest extends TestCase {

    /**
     * @var ValidationHelper
     */
    private $validationHelper;

    protected function setUp(): void {
        $this->validationHelper = new ValidationHelper();
    }

    /**
     * Test that valid dates are correctly recognized.
     */
    public function testValidDate() {
        $this->assertTrue($this->validationHelper->validateDate('2024-08-06'));
        $this->assertTrue($this->validationHelper->validateDate('2023-01-01'));
    }

    /**
     * Test that invalid dates are correctly rejected.
     */
    public function testInvalidDate() {
        $this->assertFalse($this->validationHelper->validateDate('2024-02-30')); // Invalid day
        $this->assertFalse($this->validationHelper->validateDate('2024-13-01')); // Invalid month
        $this->assertFalse($this->validationHelper->validateDate('not-a-date')); // Invalid format
    }

    /**
     * Test custom date format validation.
     */
    public function testCustomFormat() {
        $this->assertTrue($this->validationHelper->validateDate('06-08-2024', 'd-m-Y')); // Custom format
        $this->assertFalse($this->validationHelper->validateDate('2024-08-06', 'd-m-Y')); // Incorrect format
    }

}
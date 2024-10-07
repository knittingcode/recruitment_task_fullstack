<?php namespace App\Utils;

/**
 * Class ValidationHelper
 *
 * Provides utility methods for validating data.
 */
class ValidationHelper {

    /**
     * Validate the date format to ensure it matches 'Y-m-d'.
     *
     * @param string $date   The date to validate.
     * @param string $format The expected date format. Default is 'Y-m-d'.
     *
     * @return bool Returns true if the date is valid, false otherwise.
     */
    public function validateDate(string $date, string $format = 'Y-m-d'): bool {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

}
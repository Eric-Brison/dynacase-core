<?php
namespace Dcp\Utils;
/**
 * Architecture specific type manipulation.
 *
 * @package Dcp\Utils
 */
class Types
{
    /**
     * Convert given string or integer to an int32
     *
     * @param string|int $value
     * @return bool(false)|int Returns an integer in the int32 range or
     * bool(false) if not a valid integer or an out-of-range integer.
     */
    public static function to_int32($value)
    {
        if (is_string($value)) {
            /* Check expected integer format /^-?\d+$/ without regex */
            if (strlen($value) <= 0 || !ctype_digit(substr($value, ((substr($value, 0, 1) == '-') ? 1 : 0)))) {
                return false;
            }
            /*
             * Cast string to integer and check for {over,under}flow
             * by comparing the resulting string with the original string.
            */
            $int = (int)$value;
            if (strcmp($value, (string)$int) !== 0) {
                return false;
            }
            $value = $int;
        }
        if (!is_int($value)) {
            return false;
        }
        /*
         * 32 bits integers are already int32, so they are safe.
        */
        if (PHP_INT_SIZE >= 8) {
            /*
             * 64 bits integers are problematic and we need to check
             * they are in the int32 range.
            */
            $int32_max = 0x7fffffff;
            if ($value < - 1 * $int32_max - 1 /* 32 bits PHP_INT_MIN */ || $value > $int32_max /* 32 bits PHP_INT_MAX */) {
                return false;
            }
        }
        return $value;
    }
}

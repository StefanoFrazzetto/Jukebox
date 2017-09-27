<?php

namespace Lib;

use Exception;

abstract class StringUtils
{
    /**
     * Remove any non-alphanumeric character from a string.
     *
     * HTML entities are encoded, then they are converted to the applicable
     * characters, spaces replaced by underscores, and non-alphanumeric
     * character are removed.
     *
     * @param string $string The string to be clean.
     *
     * @return string The string without any non-alphanumeric characters.
     */
    public static function cleanString($string)
    {
        $string = preg_replace_callback('/(&#[0-9]+;)/', function ($m) {
            return mb_convert_encoding($m[1], 'UTF-8', 'HTML-ENTITIES');
        }, $string);
        $string = html_entity_decode($string, ENT_COMPAT, 'UTF-8');
        $string = str_replace(' ', '_', $string); // Replaces all spaces with hyphens.
        return preg_replace('/[^A-Za-z0-9_\-.]+/', '', $string); // Replace special chars with hyphens.
    }

    /**
     * Return the current datetime.
     *
     * @return string the current date time as Y-m-d H:i:s
     */
    public static function getCurrentDatetime()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Check if a string contains another string.
     *
     * @see strpos()
     *
     * @param string       $haystack The string to search in.
     * @param string|array $needle   If needle is not a string, it is converted to an integer
     *                               and applied as the ordinal value of a character.
     * @param bool         $strict
     *                               If the needle argument is an array and strict is set to true,
     *                               true will be returned only if the haystack contains all the elements
     *                               passed in the needle array.
     *
     * @throws Exception if the needle is empty.
     *
     * @return bool true if the string was found, false otherwise.
     */
    public static function contains($haystack, $needle, $strict = false)
    {
        if (empty($needle)) {
            throw new Exception('The needle cannot be empty.');
        }

        $found = 0;
        if (is_array($needle)) {
            foreach ($needle as $element) {
                if (strpos($haystack, $element) !== false) {
                    $found++;
                }
            }
        } else {
            if (strpos($haystack, $needle) !== false) {
                $found++;
            }
        }

        return $strict ? $found >= count($needle) : $found >= 1;
    }

    /**
     * Check if an array contains a string as value or part of its value.
     *
     * @param array  $array_haystack The array to search in.
     * @param string $needle         If needle is not a string, it is converted to an integer
     *                               and applied as the ordinal value of a character.
     *
     * @return bool true if the string was found, false otherwise.
     */
    public static function arrayContains($array_haystack, $needle)
    {
        $contains = false;
        foreach ($array_haystack as $key => $value) {
            if (self::contains($array_haystack[$key], $needle)) {
                $contains = true;
            }
        }

        return $contains;
    }
}

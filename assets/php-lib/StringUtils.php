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
     * Check if a string contains another string.
     *
     * @see strpos()
     *
     * @param string $haystack The string to search in.
     * @param string $needle   If needle is not a string, it is converted to an integer
     *                         and applied as the ordinal value of a character.
     * @param int    $offset
     *                         If specified, search will start this number of characters
     *                         counted from the beginning of the string. Unlike strrpos() and strripos(),
     *                         the offset cannot be negative.
     *
     * @return bool true if the string was found, false otherwise.
     *
     * @throws Exception if the needle is empty.
     */
    public static function contains($haystack, $needle, $offset = 0)
    {
        if (empty($needle)) {
            throw new Exception('The needle cannot be empty.');
        }

        return strpos($haystack, $needle, $offset) !== false ? true : false;
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

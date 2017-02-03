<?php

/**
 * Created by Stefano
 * Date: 31/10/2016
 */
abstract class StringUtils
{
    public static function cleanString($string)
    {
        $string = preg_replace_callback("/(&#[0-9]+;)/", function ($m) {
            return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
        }, $string);
        $string = html_entity_decode($string, ENT_COMPAT, "UTF-8");
        $string = str_replace(' ', '_', $string); // Replaces all spaces with hyphens.
        return preg_replace('/[^A-Za-z0-9_\-.]+/', '', $string); // Replace special chars with hyphens.
    }
}
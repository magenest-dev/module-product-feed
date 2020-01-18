<?php
/**
 * Copyright © 2020 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_productfeed extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Magenest_productfeed
 */

namespace Magenest\ProductFeed\Export\Filter;

class StringFilter
{
    /**
     * HTML Entity Decode
     *
     * Convert HTML entities to their corresponding characters.
     *
     * @param string $input
     * @return string
     */
    public static function html_entity_decode($input)
    {
        return is_string($input) ? html_entity_decode($input) : $input;
    }

    /**
     * Strip all newlines (\n, \r) from string
     *
     * @param string $input
     * @return string
     */
    public static function strip_newlines($input)
    {
        return is_string($input) ? str_replace(
            ["\n", "\r"],
            '',
            $input
        ) : $input;
    }

    /**
     * Replace each newline (\n) with html break
     *
     * @param string $input
     * @return string
     */
    public static function newline_to_br($input)
    {
        return is_string($input) ? str_replace(
            ["\n", "\r"],
            '<br />',
            $input
        ) : $input;
    }

    /**
     * Truncate string down to x words
     *
     * @param string $input
     * @param int $words
     * @return string
     */
    public static function truncatewords($input, $words = 3)
    {
        if (is_string($input)) {
            $wordlist = explode(" ", $input);

            if (count($wordlist) > $words) {
                return implode(" ", array_slice($wordlist, 0, $words));
            }
        }

        return $input;
    }

    /**
     * Split input string into an array of substrings separated by given pattern.
     *
     * @param string $input
     * @param string $pattern
     * @return array
     */
    public static function split($input, $pattern)
    {
        return is_string($input) ? explode($pattern, $input) : $input;
    }

    /**
     * Format csv column value
     *
     * @param string $input
     * @param string $delimiter
     * @param string $enclosure
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function csv($input, $delimiter = ',', $enclosure = '')
    {
        if (is_scalar($input)) {
            $input = (string)$input;
            $escapeChar = '\\';
            $str = $enclosure;
            $escaped = 0;
            $len = strlen($input);
            for ($i = 0; $i < $len; $i++) {
                if ($input[$i] == $escapeChar) {
                    $escaped = 1;
                } elseif (!$escaped && $input[$i] == $enclosure) {
                    $str .= $enclosure;
                } else {
                    $escaped = 0;
                }
                $str .= $input[$i];
            }
            $str .= $enclosure;

            $input = $str;

        }

        return $input;
    }

    /**
     * Replace
     *
     * Replaces all occurrences of a string with a substring.
     *
     * @param string $input
     * @param string $search
     * @param string $replace
     * @return string
     */
    public function replace($input, $search, $replace)
    {
        return is_string($input) ? str_replace($search, $replace, $input) : $input;
    }

    /**
     * Lowercase
     *
     * Converts a string into lowercase.
     *
     * @param string $input
     * @return string
     */
    public function lowercase($input)
    {
        return is_string($input) ? mb_strtolower($input) : $input;
    }

    /**
     * Uppercase
     *
     * Converts a string into uppercase.
     *
     * @param string $input
     * @return string
     */
    public function uppercase($input)
    {
        return is_string($input) ? mb_strtoupper($input) : $input;
    }

    /**
     * Append
     *
     * Appends characters to a string.
     *
     * @param string $input
     * @param string $suffix
     * @return string
     */
    public function append($input, $suffix)
    {
        return is_string($input) ? $input . $suffix : $input;
    }

    /**
     * Prepend
     *
     * Prepends characters to a string.
     *
     * @param string $input
     * @param string $prefix
     * @return string
     */
    public function prepend($input, $prefix)
    {
        return is_string($input) ? $prefix . $input : $input;
    }

    /**
     * Capitalize
     *
     * Capitalizes the first word in a string.
     *
     * @param string $input
     * @return string
     */
    public function capitalize($input)
    {
        return is_string($input) ? ucfirst($input) : $input;
    }

    /**
     * Escape
     *
     * Escapes a string.
     *
     * @param string $input
     * @return string
     */
    public function escape($input)
    {
        return is_string($input) ? htmlspecialchars($input) : $input;
    }

    /**
     * Newline to <br>
     *
     * Inserts a <br > linebreak HTML tag in front of each line break in a string.
     *
     * @param string $input
     * @return string
     */
    public function nl2br($input)
    {
        return is_string($input) ? nl2br($input) : $input;
    }

    /**
     * Remove
     *
     * Removes all occurrences of a substring from a string.
     *
     * @param string $input
     * @param string $text
     * @return string
     */
    public function remove($input, $text)
    {
        return is_string($input) ? str_replace($text, '', $input) : $input;
    }

    /**
     * Strip HTML tags
     *
     * Strips all HTML tags from a string.
     *
     * @param string $input
     * @return string
     */
    public function stripHtml($input)
    {
        return is_string($input) ? strip_tags($input) : $input;
    }

    /**
     * Truncate
     *
     * Truncates a string down to 'x' characters.
     *
     * @param string $input
     * @param int $len
     * @return string
     */
    public function truncate($input, $len)
    {
        return is_string($input) ? mb_substr($input, 0, intval($len)) : $input;
    }

    /**
     * Plain format
     *
     * Converts any text to plain
     *
     * @param string $input
     * @return string
     */
    public function plain($input)
    {
        if (!is_string($input)) {
            return $input;
        }
        // 194 -> 32
        $input = str_replace(' ', ' ', $input);

        $input = strip_tags($input);

        $input = str_replace('\\\'', '\'', $input);
        $input = preg_replace('/\s+/', ' ', $input);

        //{{block type="cms/block" block_id="product-3-in-1" template="cms/content.phtml"}}
        $input = preg_replace('/({{.*}})/is', '', $input);

        $input = trim($input);

        return $input;
    }

    /**
     * Evaluate php
     *
     * PHPMD @SuppressWarnings(PHPMD)
     *
     * @param string $input
     * @param string $eval
     * @return string
     */
    public function php($input, $eval)
    {
        $eval = "eval";

        return $eval('return ' . $eval . ';');
    }

    /**
     * If Empty
     *
     * @param string $input
     * @param string $default
     * @return string
     */
    public function ifEmpty($input, $default)
    {
        if (!$input || $input == '') {
            return $default;
        }

        return $input;
    }

    /**
     * Format date
     *
     * Converts a string to specified date-time format.
     *
     * @param string $input
     * @param string $format
     * @return string
     */
    public function dateFormat($input, $format = 'd.m.Y')
    {
        if (is_numeric($input)) {
            return date($format, $input);
        } else {
            return date($format, strtotime($input));
        }
    }

    /**
     * Rtrim
     *
     * Strip whitespace (or other characters) from the end of a string.
     *
     * @param string $input
     * @param string $mask
     * @return string
     */
    public function rtrim($input, $mask = ' ')
    {
        return rtrim($input, $mask);
    }

    /**
     * JSON Encode
     *
     * @param string $input
     * @return string
     */
    public function json($input)
    {
        return json_encode($input);
    }

    /**
     * Clean
     *
     * Remove all non-utf-8 characters from string
     *
     * @param string $input
     * @return string $input
     */
    public function clean($input)
    {
        $input = preg_replace('/[^(\x20-\x7F)]*/', '', $input);
        $input = preg_replace(
            '/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]' . '|[\x00-\x7F][\x80-\xBF]+' . '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*' . '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})' . '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S',
            '',
            $input
        );
        $input = preg_replace(
            '/\xE0[\x80-\x9F][\x80-\xBF]' . '|\xED[\xA0-\xBF][\x80-\xBF]/S',
            '',
            $input
        );

        return $input;
    }
}

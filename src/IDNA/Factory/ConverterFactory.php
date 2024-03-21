<?php

namespace CNIC\IDNA\Factory;

use CNIC\IDNA\Converter\UnicodeConverter;
use CNIC\IDNA\Converter\ASCIIConverter;

class ConverterFactory
{
    /**
     * Convert a domain string between Unicode and Punycode formats.
     *
     * @param string|array $keywords The domain string to convert.
     * @param array $options Additional options for the conversion process.
     * @return array Returns an associative array containing the converted domain in both IDN and Punycode formats.
     * @throws \InvalidArgumentException Throws an exception if the domain string is empty.
     */
    public static function convert($keywords, $options = [])
    {
        if (empty($keywords)) {
            throw new \InvalidArgumentException('Input parameters are missing.');
        }

        if (is_array($keywords)) {
            $translatedKeywords = [];

            foreach ($keywords as $idx => $keyword) {
                $translatedKeywords[$idx]["IDN"] = self::toUnicode($keyword, $options);
                $translatedKeywords[$idx]["PUNYCODE"] = self::toASCII($keyword, $options);
            }

            return $translatedKeywords;
        }

        $translatedKeyword["IDN"] = self::toUnicode($keywords, $options);
        $translatedKeyword["PUNYCODE"] = self::toASCII($keywords, $options);

        return $translatedKeyword;
    }

    /**
     * Convert a domain string to Unicode format.
     *
     * @param string $keyword The domain string to convert.
     * @param array $options Additional options for the conversion process.
     * @return string|false Returns the converted domain in Unicode format or false if the keyword is empty.
     */
    public static function toUnicode($keyword, $options = [])
    {
        if (empty($keyword)) {
            return false;
        }

        if (mb_strpos($keyword, '.') === false) {
            return self::handleConversion($keyword, $options, 'toUnicode');
        } else {
            $domainArray = explode(".", trim($keyword));
            $options["domain"] = $keyword;
            foreach ($domainArray as &$tmpKeyword) {
                if (ASCIIConverter::check($tmpKeyword) || UnicodeConverter::check($tmpKeyword)) {
                    $tmpKeyword = UnicodeConverter::convert($tmpKeyword, $options);
                }
            }

            return implode(".", $domainArray);
        }
    }

    /**
     * Convert a domain string to Punycode format.
     *
     * @param string $keyword The domain string to convert.
     * @param array $options Additional options for the conversion process.
     * @return string|false Returns the converted domain in Punycode format or false if the keyword is empty.
     */
    public static function toASCII($keyword, $options = [])
    {
        if (empty($keyword)) {
            return false;
        }

        if (mb_strpos($keyword, '.') === false) {
            return self::handleConversion($keyword, $options, 'toASCII');
        } else {
            $domainArray = explode(".", trim($keyword));
            $options["domain"] = $keyword;
            foreach ($domainArray as &$tmpKeyword) {
                $tmpKeyword = UnicodeConverter::decode($tmpKeyword);
                if (UnicodeConverter::check($tmpKeyword)) {
                    $tmpKeyword = ASCIIConverter::convert($tmpKeyword, $options);
                }
            }

            return implode(".", $domainArray);
        }
    }

    /**
     * Handle conversion of a keyword between Unicode and Punycode formats.
     *
     * @param string $keyword The domain string to convert.
     * @param array $options Additional options for the conversion process.
     * @param string $method The conversion method to use.
     * @return string Returns the converted domain string.
     */
    private static function handleConversion($keyword, $options, $method)
    {
        if (!isset($options["retry"]) && UnicodeConverter::check($keyword)) {
            $tmpKeyword = UnicodeConverter::convert($keyword, $options);
            if (mb_strpos($tmpKeyword, '.') !== false) {
                return self::$method($tmpKeyword, array_merge($options, ["retry" => true]));
            }
        }

        if (ASCIIConverter::check($keyword) || UnicodeConverter::check($keyword)) {
            return UnicodeConverter::convert($keyword, $options);
        }

        return $keyword;
    }

    /**
     * Check if the provided top-level domain (TLD) is non-transitional.
     *
     * @param string $keyword The domain string to check.
     * @param array $options Additional options for the conversion process.
     * @return bool Returns true if the TLD is non-transitional, false otherwise.
     */
    public static function transitionalProcessing($keyword, $options = [])
    {
        if (isset($options["transitionalProcessing"])) {
            return $options["transitionalProcessing"];
        }

        return mb_ereg("\./(be|ca|de|fr|pm|re|swiss|tf|wf|yt)\.?$/i", $options["domain"] ?? $keyword) !== false;
    }
}

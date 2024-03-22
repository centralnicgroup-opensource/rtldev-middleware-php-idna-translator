<?php

namespace CNIC\IDNA\Converter;

use CNIC\IDNA\Factory\ConverterFactory;

class UnicodeConverter implements ConversionInterface
{
    /**
     * Convert the keyword to Unicode format.
     *
     * @param string $keyword The keyword to convert
     * @return string Returns the IDN representation of the keyword
     */
    public static function convert($keyword, $options)
    {
        $transitionalProcessing = ConverterFactory::transitionalProcessing($keyword, $options);

        $idn = idn_to_utf8(
            self::decode($keyword),
            $transitionalProcessing ? IDNA_NONTRANSITIONAL_TO_UNICODE : IDNA_DEFAULT,
            INTL_IDNA_VARIANT_UTS46
        );
        if ($idn !== false) {
            return $idn; // If successful, return the IDN representation
        }
        return $keyword; // If conversion fails, return the normalized keyword
    }

    /**
     * Check if the keyword is in Unicode format.
     *
     * @param string $keyword The keyword to check
     * @return bool True if the keyword is in IDN format, false otherwise
     */
    public static function check($keyword)
    {
        return mb_ereg(
            '[^\x00-\x7F\x{FF00}-\x{FFFF}]',
            $keyword
        ) !== false; // Check if keyword contains non-ASCII characters
    }

    /**
     * Check if a string contains Unicode characters represented by escape sequences.
     *
     * Unicode characters can be represented in PHP strings using escape sequences like \uXXXX.
     * This function checks if the input string contains any Unicode characters.
     *
     * @param string $str The input string to check.
     * @return bool Returns true if the string contains Unicode characters, false otherwise.
     */
    public static function containsUnicodeCharacters($str)
    {
        // Check for Unicode characters
        return preg_match('/[\x{0080}-\x{10FFFF}]/u', self::decode($str)) !== false;
    }

    /**
     * Convert Unicode escape sequences to their corresponding characters.
     *
     * @param string $unicodeString String with Unicode escape sequences
     * @return string Converted string with actual Unicode characters
     */
    public static function decode($unicodeString)
    {
        // Decode Unicode escape sequences
        return mb_strtolower(json_decode('"' . $unicodeString . '"', true, 512, JSON_UNESCAPED_UNICODE));
    }
}

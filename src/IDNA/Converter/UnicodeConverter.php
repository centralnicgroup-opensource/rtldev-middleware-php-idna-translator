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
            '[^\x00-\x7F\x{FF00}-\x{FFFF}]|\\\\u[0-9A-Fa-f]{4}',
            $keyword
        ) !== false; // Check if keyword contains non-ASCII characters
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

<?php

declare(strict_types=1);

namespace CNIC\IDNA\Converter;

use CNIC\IDNA\Factory\ConverterFactory;

final class UnicodeConverter implements ConversionInterface
{
    /**
     * Convert the keyword to Unicode format.
     *
     * @param string $keyword The keyword to convert
     * @param array<string, mixed> $options Additional options for the conversion process
     * @return string Returns the IDN representation of the keyword, or the original keyword if conversion fails
     */
    #[\Override]
    public static function convert(string $keyword, array $options): string
    {
        $transitionalProcessing = ConverterFactory::transitionalProcessing($keyword, $options);

        $idn = idn_to_utf8(
            self::decode($keyword),
            $transitionalProcessing ? IDNA_NONTRANSITIONAL_TO_UNICODE : IDNA_DEFAULT,
            INTL_IDNA_VARIANT_UTS46
        );

        return $idn !== false ? $idn : $keyword;
    }

    /**
     * Check if the keyword is in Unicode format.
     *
     * @param string $keyword The keyword to check
     * @return bool True if the keyword is in IDN format, false otherwise
     */
    #[\Override]
    public static function check(string $keyword): bool
    {
        // Single-quoted: double quotes would decode \x00/\x7F to raw bytes before mb_ereg sees the pattern.
        return mb_ereg(
            '[^\x00-\x7F\x{FF00}-\x{FFFF}]',
            $keyword
        ); // Check if keyword contains non-ASCII characters
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
    public static function containsUnicodeCharacters(string $str): bool
    {
        // Check for Unicode characters
        return preg_match("/[\x{0080}-\x{10FFFF}]/u", self::decode($str)) === 1;
    }

    /**
     * Convert Unicode escape sequences to their corresponding characters.
     *
     * @param string $unicodeString String with Unicode escape sequences
     * @return string Converted string with actual Unicode characters
     */
    public static function decode(string $unicodeString): string
    {
        $decoded = json_decode("\"" . $unicodeString . "\"", true);
        \assert(\is_string($decoded) || $decoded === null);

        return mb_strtolower($decoded ?? "");
    }
}

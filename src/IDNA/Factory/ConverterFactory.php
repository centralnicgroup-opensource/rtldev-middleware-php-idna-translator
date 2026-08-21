<?php

declare(strict_types=1);

namespace CNIC\IDNA\Factory;

use CNIC\IDNA\Converter\ASCIIConverter;
use CNIC\IDNA\Converter\UnicodeConverter;

final class ConverterFactory
{
    /**
     * Convert a domain string between Unicode and Punycode formats.
     *
     * @param string|array<array-key, string> $keywords The domain string (or list of domain strings) to convert.
     * @param array<string, mixed> $options Additional options for the conversion process.
     * @return ($keywords is array ? array<array-key, array{idn: string|false, punycode: string|false}> : array{idn: string|false, punycode: string|false})
     *     Returns an associative array containing the converted domain in both IDN and Punycode formats,
     *     or, when a list of domains is given, a list of such associative arrays keyed like the input.
     *
     * @api
     */
    public static function convert(string|array $keywords, array $options = []): array
    {
        if (is_string($keywords)) {
            return [
                "idn" => self::toUnicode($keywords, $options),
                "punycode" => self::toASCII($keywords, $options),
            ];
        }

        return self::convertBulk($keywords, $options);
    }

    /**
     * Convert a list of domain strings between Unicode and Punycode formats, preserving the input keys.
     *
     * @template TKey of array-key
     * @param array<TKey, string> $keywords The domain strings to convert.
     * @param array<string, mixed> $options Additional options for the conversion process.
     * @return array<TKey, array{idn: string|false, punycode: string|false}>
     *     Returns one associative array per input keyword, keyed like the input,
     *     each containing the converted domain in both IDN and Punycode formats.
     *
     * @api
     */
    public static function convertBulk(array $keywords, array $options = []): array
    {
        $translatedKeywords = [];

        foreach ($keywords as $idx => $keyword) {
            $translatedKeywords[$idx] = [
                "idn" => self::toUnicode($keyword, $options),
                "punycode" => self::toASCII($keyword, $options),
            ];
        }

        return $translatedKeywords;
    }

    /**
     * Convert a domain string to Unicode format.
     *
     * @param string $keyword The domain string to convert.
     * @param array<string, mixed> $options Additional options for the conversion process.
     * @return string|false Returns the converted domain in Unicode format or false if the keyword is empty.
     *
     * @api
     */
    public static function toUnicode(string $keyword, array $options = []): string|false
    {
        if ($keyword === "") {
            return false;
        }

        if (!str_contains($keyword, ".")) {
            return self::handleConversion($keyword, $options, "toUnicode");
        }

        $domainArray = explode(".", trim($keyword));
        $options["domain"] = $keyword;

        foreach ($domainArray as &$tmpKeyword) {
            if (ASCIIConverter::check($tmpKeyword)) {
                // Already-decoded output must not be run through conversion again below:
                // re-converting it would re-apply transitional mapping to a result that
                // is already final, corrupting it (e.g. "faß" would become "fass").
                $tmpKeyword = UnicodeConverter::convert($tmpKeyword, $options);
            } elseif (UnicodeConverter::containsUnicodeCharacters($tmpKeyword)) {
                $tmpKeyword = UnicodeConverter::decode($tmpKeyword);
                $tmpKeyword = UnicodeConverter::convert($tmpKeyword, $options);
            }
        }
        unset($tmpKeyword);

        return implode(".", $domainArray);
    }

    /**
     * Convert a domain string to Punycode format.
     *
     * @param string $keyword The domain string to convert.
     * @param array<string, mixed> $options Additional options for the conversion process.
     * @return string|false Returns the converted domain in Punycode format or false if the keyword is empty.
     *
     * @api
     */
    public static function toASCII(string $keyword, array $options = []): string|false
    {
        if ($keyword === "") {
            return false;
        }

        if (!str_contains($keyword, ".")) {
            return self::handleConversion($keyword, $options, "toASCII");
        }

        $domainArray = explode(".", trim($keyword));
        $options["domain"] = $keyword;

        foreach ($domainArray as &$tmpKeyword) {
            $tmpKeyword = UnicodeConverter::decode($tmpKeyword);
            if (UnicodeConverter::check($tmpKeyword)) {
                $tmpKeyword = ASCIIConverter::convert($tmpKeyword, $options);
            }
        }
        unset($tmpKeyword);

        return implode(".", $domainArray);
    }

    /**
     * Handle conversion of a keyword between Unicode and Punycode formats.
     *
     * @param string $keyword The domain string to convert.
     * @param array<string, mixed> $options Additional options for the conversion process.
     * @param string $method The conversion method to use, either "toUnicode" or "toASCII".
     * @return string|false Returns the converted domain string.
     */
    private static function handleConversion(string $keyword, array $options, string $method): string|false
    {
        if (!isset($options["retry"]) && UnicodeConverter::check($keyword)) {
            $tmpKeyword = UnicodeConverter::convert($keyword, $options);
            if (str_contains($tmpKeyword, ".")) {
                $retryOptions = array_merge($options, ["retry" => true]);

                return $method === "toASCII"
                    ? self::toASCII($tmpKeyword, $retryOptions)
                    : self::toUnicode($tmpKeyword, $retryOptions);
            }
        }

        $tmpKeyword = $keyword;
        if (UnicodeConverter::containsUnicodeCharacters($tmpKeyword)) {
            $tmpKeyword = UnicodeConverter::decode($tmpKeyword);
            $tmpKeyword = UnicodeConverter::convert($tmpKeyword, $options);
        }
        if ($method === "toASCII") {
            if (UnicodeConverter::check($tmpKeyword)) {
                $tmpKeyword = ASCIIConverter::convert($tmpKeyword, $options);
            }
        } elseif (ASCIIConverter::check($tmpKeyword)) {
            $tmpKeyword = UnicodeConverter::convert($tmpKeyword, $options);
        }

        return $tmpKeyword;
    }

    /**
     * Determine whether transitional IDNA processing should be applied to the given domain.
     *
     * @param string $keyword The domain string to check.
     * @param array<string, mixed> $options Additional options for the conversion process.
     * @return bool Returns true if transitional processing should be applied, false for non-transitional processing.
     *
     * @api
     */
    public static function transitionalProcessing(string $keyword, array $options = []): bool
    {
        if (isset($options["transitionalProcessing"])) {
            return (bool) $options["transitionalProcessing"];
        }

        $domain = isset($options["domain"]) && is_string($options["domain"])
            ? $options["domain"]
            : $keyword;

        // These ccTLD registries require non-transitional processing (deviation characters kept as-is).
        return !mb_eregi(
            "\.(art|be|ca|de|fr|pm|re|swiss|tf|wf|yt)\.?$",
            $domain
        );
    }

    /**
     * Map UTS46 "deviation" characters to their transitional-processing equivalents.
     *
     * Current ICU versions no longer distinguish IDNA_DEFAULT from
     * IDNA_NONTRANSITIONAL_TO_ASCII/TO_UNICODE, so transitional processing is
     * implemented here explicitly rather than delegated to ICU, keeping
     * results stable across ICU versions.
     *
     * @param string $keyword The keyword to map.
     * @return string The keyword with deviation characters mapped.
     *
     * @internal
     */
    public static function applyTransitionalMapping(string $keyword): string
    {
        return strtr($keyword, [
            "\u{00DF}" => "ss",
            "\u{03C2}" => "\u{03C3}",
            "\u{200C}" => "",
            "\u{200D}" => "",
        ]);
    }
}

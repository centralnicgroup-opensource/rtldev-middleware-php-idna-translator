<?php

declare(strict_types=1);

namespace CNIC\IDNA\Converter;

use CNIC\IDNA\Factory\ConverterFactory;

final class ASCIIConverter implements ConversionInterface
{
    /**
     * Convert the keyword to ASCII format.
     *
     * @param string $keyword The keyword to convert
     * @param array<string, mixed> $options Additional options for the conversion process
     * @return string Returns the Punycode representation of the keyword, or the original keyword if conversion fails
     */
    #[\Override]
    public static function convert(string $keyword, array $options): string
    {
        $transitionalProcessing = ConverterFactory::transitionalProcessing($keyword, $options);

        // Convert domain to Punycode
        $punycode = idn_to_ascii(
            $keyword,
            $transitionalProcessing ? IDNA_NONTRANSITIONAL_TO_ASCII : IDNA_DEFAULT,
            INTL_IDNA_VARIANT_UTS46
        );

        return $punycode !== false ? $punycode : $keyword;
    }

    /**
     * Check if the keyword is in ASCII format.
     *
     * @param string $keyword The keyword to check
     * @return bool True if the keyword is in Punycode format, false otherwise
     */
    #[\Override]
    public static function check(string $keyword): bool
    {
        return str_starts_with($keyword, "xn--");
    }
}

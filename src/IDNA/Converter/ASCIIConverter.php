<?php

namespace CNIC\IDNA\Converter;

use CNIC\IDNA\Factory\ConverterFactory;

class ASCIIConverter implements ConversionInterface
{
    /**
     * Convert the keyword to ASCII format.
     *
     * @param string $keyword The keyword to convert
     * @return string Returns the Punycode representation of the keyword
     */
    public static function convert($keyword, $options)
    {
        $transitionalProcessing = ConverterFactory::transitionalProcessing($keyword, $options);

        // Convert domain to Punycode
        $punycode = idn_to_ascii(
            $keyword,
            $transitionalProcessing ? IDNA_NONTRANSITIONAL_TO_ASCII : IDNA_DEFAULT,
            INTL_IDNA_VARIANT_UTS46
        );

        if ($punycode !== false) {
            return $punycode; // If successful, return the Punycode representation
        }
        return $keyword; // If conversion fails, return the normalized keyword
    }

    /**
     * Check if the keyword is in ASCII format.
     *
     * @param string $keyword The keyword to check
     * @return bool True if the keyword is in Punycode format, false otherwise
     */
    public static function check($keyword)
    {
        return mb_ereg('^xn--', $keyword) !== false; // Check if keyword starts with "xn--"
    }
}

<?php

namespace CNIC\IDNA;

/**
 * Class IDNATranslator
 * Utility class for converting domain names between IDN and Punycode formats.
 */
class IDNATranslator
{
    /**
     * Convert domain name to IDN + Punycode if necessary.
     *
     * @param array|string $domain Domain name (or TLD)
     * @param array $options Additional options for the conversion process
     * @return array|string Returns both IDN and Punycode variants by default.
     */
    public static function convert($domain, $options = [])
    {
        if (empty($domain)) {
            return false;
        }

        if (is_array($domain)) {
            return self::convertBulk($domain);
        }

        // Check if a dot exists in the domain
        if (strpos($domain, '.') === false) {
            // if dot does not found check if the dot is a unicode character and retry again
            if (!isset($options["retry"])) {
                return self::convert(self::convert($domain, $options["transitionalProcessing"] ?? false), array_merge($options, ["retry" => true])); //convert to unicode then try again
            }
            return $domain; // If not, return the original domain
        }

        // Split domain into Second-Level Domain (SLD) and Top-Level Domain (TLD)
        list($sld, $tld) = explode(".", trim($domain), 2);

        // Check if both SLD and TLD are already in neither IDN nor Punycode format
        if (!self::isUnicode($sld) && !self::isASCII($sld) && !self::isUnicode($tld) && !self::isASCII($tld)) {
            return $domain; // If so, return the original domain
        }

        $transitionalProcessing = self::isNonTransitionalTld($tld, $options);

        // Convert both SLD and TLD to IDN and Punycode formats
        $convertDomain["IDN"] = self::convertToUnicode($sld, $transitionalProcessing) . "." . self::convertToUnicode($tld, $transitionalProcessing);
        $convertDomain["PUNYCODE"] = self::convertToASCII($sld, $transitionalProcessing) . "." . self::convertToASCII($tld, $transitionalProcessing);

        return $convertDomain; // Return the array containing both IDN and Punycode variants
    }

    /**
     * Get the Unicode variant of the domain name.
     *
     * @param string $domain Domain name
     * @return string Returns the IDN variant of the domain name.
     */
    public static function toUnicode($domain, $options = [])
    {
        if (empty($domain)) {
            return false;
        }

        // Check if a dot exists in the domain
        if (strpos($domain, '.') === false) {
            // if dot does not found check if the dot is a unicode character and retry again
            if (!isset($options["retry"])) {
                return self::toUnicode(self::convertToUnicode($domain, $options["transitionalProcessing"] ?? false), array_merge($options, ["retry" => true])); //convert to unicode then try again
            }
            return $domain; // If not, return the original domain
        }

        // Split domain into Second-Level Domain (SLD) and Top-Level Domain (TLD)
        list($sld, $tld) = explode(".", trim($domain), 2);

        // Convert either SLD or TLD (whichever is in ASCII format) to Unicode
        $sld = self::convertToUnicode($sld, $options);
        $tld = self::convertToUnicode($tld, $options);

        // Return the domain in IDN format
        return $sld . "." . $tld;
    }


    /**
     * Get the ASCII variant of the domain name.
     *
     * @param string $domain Domain name
     * @param array $options Additional options for the conversion process
     * @return string Returns the IDN variant of the domain name.
     */
    public static function toASCII($domain, $options = [])
    {
        if (empty($domain)) {
            return false;
        }

        // Check if a dot exists in the domain
        if (strpos($domain, '.') === false) {
            // if dot does not found check if the dot is a unicode character and retry again
            if (!isset($options["retry"])) {
                return self::toASCII(self::convertToUnicode($domain, $options["transitionalProcessing"] ?? false), array_merge($options, ["retry" => true])); //convert to unicode then try again
            }
            return $domain; // If not, return the original domain
        }

        // Split domain into Second-Level Domain (SLD) and Top-Level Domain (TLD)
        list($sld, $tld) = explode(".", trim($domain), 2);

        // Check if neither SLD nor TLD are in Unicode format
        if (!self::isUnicode($sld) && !self::isUnicode($tld) && !isset($options["transitionalProcessing"])) {
            return strtolower($domain); // If so, return the original domain
        }

        $transitionalProcessing = self::isNonTransitionalTld($tld, $options);

        // Convert either SLD or TLD (whichever is in Punycode format) to IDN
        $sld = self::convertToASCII($sld, $transitionalProcessing);
        $tld = self::convertToASCII($tld, $transitionalProcessing);

        // Return the domain in IDN format
        return $sld . "." . $tld;
    }

    /**
     * Check if the keyword is in ASCII format.
     *
     * @param string $keyword The keyword to check
     * @return bool True if the keyword is in Punycode format, false otherwise
     */
    public static function isASCII($keyword)
    {
        return preg_match('/^xn--/', $keyword) === 1; // Check if keyword starts with "xn--"
    }

    /**
     * Check if the keyword is in Unicode format.
     *
     * @param string $keyword The keyword to check
     * @return bool True if the keyword is in IDN format, false otherwise
     */
    public static function isUnicode($keyword)
    {
        return preg_match('/[^\x00-\x7F]/', self::decodeUnicode($keyword)) !== 0; // Check if keyword contains non-ASCII characters
    }

    /**
     * Convert domain names to Unicode + ASCII if necessary
     * @param array $domains Array of domain names (or TLDs)
     * @param bool $returnIDNOnly Optional flag to return only IDN variants
     * @return array|string Array of converted domains (IDN and Punycode) or IDN variant only
     */
    private static function convertBulk(array $domains)
    {
        if (empty($domains)) {
            return false;
        }

        $convertedDomains = [];

        foreach ($domains as $domain) {
            $convertedDomain = self::convert($domain);
            $convertedDomains[$domain] = $convertedDomain;
        }

        return $convertedDomains;
    }

    /**
     * Convert the keyword to ASCII format.
     *
     * @param string $keyword The keyword to convert
     * @return string Returns the Punycode representation of the keyword
     */
    private static function convertToASCII($keyword, $transitionalProcessing)
    {
        // Convert domain to Punycode
        $punycode = idn_to_ascii(
            self::decodeUnicode($keyword),
            $transitionalProcessing ? IDNA_NONTRANSITIONAL_TO_ASCII : IDNA_DEFAULT,
            INTL_IDNA_VARIANT_UTS46
        );
        if ($punycode !== false) {
            return $punycode; // If successful, return the Punycode representation
        }
        return $keyword; // If conversion fails, return the original keyword
    }

    /**
     * Convert the keyword to Unicode format.
     *
     * @param string $keyword The keyword to convert
     * @return string Returns the IDN representation of the keyword
     */
    private static function convertToUnicode($keyword, $transitionalProcessing = false)
    {
        // Convert Punycode (ASCII) back to IDN (Unicode)
        $idn = idn_to_utf8(
            self::decodeUnicode($keyword),
            $transitionalProcessing ? IDNA_NONTRANSITIONAL_TO_UNICODE : IDNA_DEFAULT,
            INTL_IDNA_VARIANT_UTS46
        );
        if ($idn !== false) {
            return $idn; // If successful, return the IDN representation
        }
        return $keyword; // If conversion fails, return the original keyword
    }

    /**
     * Check if the provided top-level domain (TLD) is non-transitional.
     *
     * @param string $tld The top-level domain (TLD) to check.
     * @param array $options Additional options for the conversion process
     * @return bool Returns true if the TLD is non-transitional, false otherwise.
     */
    private static function isNonTransitionalTld($tld, $options = [])
    {
        if (isset($options["transitionalProcessing"])) {
            return $options["transitionalProcessing"];
        }

        // Regular expression to match non-transitional TLDs
        // Case-insensitive matching enabled with the "i" modifier
        return (bool)preg_match("/(be|ca|de|fr|pm|re|swiss|tf|wf|yt)\.?$/i", $tld);
    }

    /**
     * Convert Unicode escape sequences to their corresponding characters.
     *
     * @param string $unicodeString String with Unicode escape sequences
     * @return string Converted string with actual Unicode characters
     */
    private static function decodeUnicode($unicodeString)
    {
        // Decode Unicode escape sequences
        return mb_strtolower(json_decode('"' . $unicodeString . '"', true, 512, JSON_UNESCAPED_UNICODE));
    }
}

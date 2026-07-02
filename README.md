# IDN Converter - Convert IDN to Punycode or Punycode to IDN - PHP Library

The IDN Converter PHP Library provides a simple and efficient solution for converting Internationalized Domain Names (IDNs) to Punycode and vice versa in PHP applications. With the `ConverterFactory` class, developers can seamlessly handle domain string conversions between Unicode and Punycode formats, ensuring compatibility and consistency across different systems.

### Key Features:
- Convert domain strings to Unicode and Punycode formats effortlessly.
- Supports conversion of single domain strings as well as bulk conversion of multiple domains.
- Intuitive API with easy-to-use methods for domain conversion.
- Comprehensive API documentation for easy integration and usage.

**Get Started:** Install the library via Composer and follow the usage examples in the README to start converting domain strings efficiently in your PHP projects.

## Requirements

- PHP 8.3 or higher
- `ext-intl`, `ext-mbstring` and `ext-json`

## Installation

You can install the IDN Converter PHP Library via Composer. Run the following command in your terminal:

```bash
composer require centralnic-reseller/idn-converter
```

## Use Cases

- **Domain Conversion**: Convert domain strings between Unicode and Punycode formats to ensure compatibility and consistency across different systems.

## Usage

### 1. Convert a Domain String to Unicode

```php
<?php

use CNIC\IDNA\Factory\ConverterFactory;

// Convert a domain string to Unicode format
$domain = "example.com";
$unicodeDomain = ConverterFactory::toUnicode($domain);
echo "Unicode Domain: $unicodeDomain\n";
```

### 2. Convert a Domain String to Punycode

```php
<?php

use CNIC\IDNA\Factory\ConverterFactory;

// Convert a domain string to Punycode format
$unicodeDomain = "example.com";
$punycodeDomain = ConverterFactory::toASCII($unicodeDomain);
echo "Punycode Domain: $punycodeDomain\n";
```

### 3. Convert Multiple Domain Strings

```php
<?php

use CNIC\IDNA\Factory\ConverterFactory;

// Convert multiple domain strings to Unicode and Punycode formats
$domains = ["example.com", "münchen.de", "рф.ru"];
$convertedDomains = ConverterFactory::convertBulk($domains);
foreach ($convertedDomains as $domain) {
    echo "Unicode Domain: {$domain['idn']}, Punycode Domain: {$domain['punycode']}\n";
}
```

## API Documentation

```php
### `ConverterFactory::toUnicode($keyword, $options = [])`

Converts a domain string to Unicode format.

- **Parameters:**
  - `$keyword` (string): The domain string to convert.
  - `$options` (array): Additional options for the conversion process (optional).
- **Returns:** The converted domain in Unicode format, or `false` if the keyword is empty.

### `ConverterFactory::toASCII($keyword, $options = [])`

Converts a domain string to Punycode format.

- **Parameters:**
  - `$keyword` (string): The domain string to convert.
  - `$options` (array): Additional options for the conversion process (optional).
- **Returns:** The converted domain in Punycode format, or `false` if the keyword is empty.

### `ConverterFactory::convert($keywords, $options = [])`

Converts a single domain string (or a list of domain strings) to both Unicode and Punycode formats.

- **Parameters:**
  - `$keywords` (string|array): The domain string, or a list of domain strings, to convert.
  - `$options` (array): Additional options for the conversion process (optional).
- **Returns:** An array with `idn` and `punycode` entries; for a list input, one such array per input keyword, keyed like the input.

### `ConverterFactory::convertBulk($keywords, $options = [])`

Converts a list of domain strings to both Unicode and Punycode formats, preserving the input keys.

- **Parameters:**
  - `$keywords` (array): The domain strings to convert.
  - `$options` (array): Additional options for the conversion process (optional).
- **Returns:** One array with `idn` and `punycode` entries per input keyword, keyed like the input.
```

## Development

After cloning the repository, install the dev dependencies with `composer install`. The following Composer scripts are available:

| Script | Description |
| --- | --- |
| `composer phpcs` | Check coding style against PSR-12 with PHP_CodeSniffer |
| `composer codefix` | Automatically fix coding style violations |
| `composer phpstan` | Run PHPStan static analysis (level 8) |
| `composer psalm` | Run Psalm static analysis (level 2) |
| `composer lint` | Run PHPCS, PHPStan and Psalm together |
| `composer rector` | Preview the changes Rector would make, without applying them |
| `composer rector:fix` | Apply automated modernization rules with Rector |
| `composer test` | Run the PHPUnit test suite |
| `composer coverage` | Run the test suite with HTML coverage output |

**License:** This library is distributed under the MIT License, allowing for flexibility in usage and modification.
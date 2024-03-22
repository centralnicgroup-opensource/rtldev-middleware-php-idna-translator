# IDN Converter - Convert IDN to Punycode or Punycode to IDN - PHP Library

The IDN Converter PHP Library provides a simple and efficient solution for converting Internationalized Domain Names (IDNs) to Punycode and vice versa in PHP applications. With the `ConverterFactory` class, developers can seamlessly handle domain string conversions between Unicode and Punycode formats, ensuring compatibility and consistency across different systems.

### Key Features:
- Convert domain strings to Unicode and Punycode formats effortlessly.
- Supports conversion of single domain strings as well as bulk conversion of multiple domains.
- Intuitive API with easy-to-use methods for domain conversion.
- Comprehensive API documentation for easy integration and usage.

**Get Started:** Install the library via Composer and follow the usage examples in the README to start converting domain strings efficiently in your PHP projects.

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
$convertedDomains = ConverterFactory::convert($domains);
foreach ($convertedDomains as $domain) {
    echo "Unicode Domain: {$domain['IDN']}, Punycode Domain: {$domain['PUNYCODE']}\n";
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

**License:** This library is distributed under the MIT License, allowing for flexibility in usage and modification.

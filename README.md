# ConverterFactory

The `ConverterFactory` class provides functionality for converting domain strings between Unicode and Punycode formats.

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
echo "Punycode Domain: $
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

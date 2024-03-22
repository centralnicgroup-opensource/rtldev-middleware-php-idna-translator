<?php
include ('vendor/autoload.php');

use CNIC\IDNA\Factory\ConverterFactory;

// Example 1: Domain with ASCII characters
$domain = 'ＡＢＣ・日本.co.jp';
$result = ConverterFactory::convert($domain, ["transitionalProcessing" => false]);
print_r($result);
echo "\n";

// Example 2: Convert to unicode
$domain = '日本｡co｡jp';
$result = ConverterFactory::toUnicode($domain);
print_r($result);
echo "\n";
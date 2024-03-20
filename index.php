<?php
include ('lib/IDNATranslator.php');

use CNIC\IDNA\IDNATranslator;


// Example 1: Domain with ASCII characters
$domain = 'faß.de';
$result = IDNATranslator::convert($domain, ["transitionalProcessing" => false]);
print_r($result);
echo "\n";

// Example 2: Convert to unicode
$domain = '日本｡co｡jp';
$result = IDNATranslator::toUnicode($domain);
print_r($result);
echo "\n";
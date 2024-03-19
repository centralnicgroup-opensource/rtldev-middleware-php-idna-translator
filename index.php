<?php
include ('lib/IDNATranslator.php');

use CNIC\IDNA\IDNATranslator;


// Example 1: Domain with ASCII characters
$domain1 = 'faß.de';
$result1 = IDNATranslator::toASCII($domain1, ["transitionalProcessing" => false]);
print_r($result1);
echo "\n";

// // Example 1: Convert to unicode
// $domain1 = 'نامه\u200Cای.de';
// $result1 = IDNATranslator::toUnicode($domain1);
// print_r($result1);
// echo "\n";

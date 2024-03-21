<?php

namespace CNIC\IDNA\Converter;

interface ConversionInterface
{
    public static function convert($keyword, $options);
    public static function check($keyword);
}

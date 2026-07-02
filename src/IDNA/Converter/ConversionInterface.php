<?php

declare(strict_types=1);

namespace CNIC\IDNA\Converter;

interface ConversionInterface
{
    /**
     * @param array<string, mixed> $options Additional options for the conversion process.
     */
    public static function convert(string $keyword, array $options): string;

    public static function check(string $keyword): bool;
}

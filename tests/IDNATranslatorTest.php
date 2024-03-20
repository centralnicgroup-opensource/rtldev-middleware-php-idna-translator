<?php

namespace CNIC\IDNA\Tests;

use PHPUnit\Framework\TestCase;
use CNIC\IDNA\IDNATranslator;

class IDNATranslatorTest extends TestCase
{
    private static $data = [
        'convert' => [
            'öbb.at' => 'xn--bb-eka.at',
            'faß.de' => 'xn--fa-hia.de',
        ],
        'toAscii' => [
            '' => '',
            '\ud83d\udca9.at' => 'xn--ls8h.at',
            '\ud87e\udcca.at' => 'xn--w60j.at',
            '\udb40\udd00\ud87e\udcca.at' => 'xn--w60j.at',
        ],
        'toAsciiWithTransitional' => [
            'fass.de' => 'fass.de',
            '₹.com' => 'xn--yzg.com',
            '𑀓.com' => 'xn--n00d.com',
            'öbb.at' => 'xn--bb-eka.at',
            'ÖBB.at' => 'xn--bb-eka.at',
            'ȡog.de' => 'xn--og-09a.de',
            '☕.de' => 'xn--53h.de',
            'I♥NY.de' => 'xn--iny-zx5a.de',
            'ＡＢＣ・日本.co.jp' => 'xn--abc-rs4b422ycvb.co.jp',
            '日本｡co｡jp' => 'xn--wgv71a.co.jp',
            '日本｡co．jp' => 'xn--wgv71a.co.jp',
            'x\u0327\u0301.de' => 'xn--x-xbb7i.de',
            'x\u0301\u0327.de' => 'xn--x-xbb7i.de',
            'عربي.de' => 'xn--ngbrx4e.de',
            'نامهای.de' => 'xn--mgba3gch31f.de',
            'fäß.de' => 'xn--f-qfao.de',
            'faß.de' => 'xn--fa-hia.de',
            'xn--fa-hia.de' => 'xn--fa-hia.de',
            'σόλος.gr' => 'xn--wxaijb9b.gr',
            'Σόλος.gr' => 'xn--wxaijb9b.gr',
            'ΣΌΛΟΣ.grﻋﺮﺑﻲ.de' => 'xn--wxaikc6b.xn--gr-gtd9a1b0g.de',
            'نامه\u200Cای.de' => 'xn--mgba3gch31f060k.de',
        ],
        'toAsciiWithoutTransitional' => [
            'σόλος.gr' => 'xn--wxaikc6b.gr',
            'Σόλος.gr' => 'xn--wxaikc6b.gr',
            'ΣΌΛΟΣ.grﻋﺮﺑﻲ.de' => 'xn--wxaikc6b.xn--gr-gtd9a1b0g.de',
            'fäß.de' => 'xn--fss-qla.de',
            'faß.de' => 'fass.de',
            'xn--bb-eka.at' => 'xn--bb-eka.at',
            'XN--BB-EKA.AT' => 'xn--bb-eka.at',
            'fass.de' => 'fass.de',
            'not=std3' => 'not=std3',
            'öbb.at' => 'xn--bb-eka.at',
            '₹.com' => 'xn--yzg.com',
            '𑀓.com' => 'xn--n00d.com',
            'ÖBB.at' => 'xn--bb-eka.at',
            'ȡog.de' => 'xn--og-09a.de',
            '☕.de' => 'xn--53h.de',
            'I♥NY.de' => 'xn--iny-zx5a.de',
            'ＡＢＣ・日本.co.jp' => 'xn--abc-rs4b422ycvb.co.jp',
            '日本｡co｡jp' => 'xn--wgv71a.co.jp',
            '日本｡co．jp' => 'xn--wgv71a.co.jp',
            'x\u0327\u0301.de' => 'xn--x-xbb7i.de',
            'x\u0301\u0327.de' => 'xn--x-xbb7i.de',
            'عربي.de' => 'xn--ngbrx4e.de',
            'نامهای.de' => 'xn--mgba3gch31f.de',
        ],
        'toUnicode' => [
            'öbb.at' => 'öbb.at',
            'xn--bb-eka.at' => 'öbb.at',
            'faß.de' => 'faß.de',
            'fass.de' => 'fass.de',
            'xn--fa-hia.de' => 'faß.de',
            'fäß.de' => 'fäß.de',
            '₹.com' => '₹.com',
            '𑀓.com' => '𑀓.com',
            'ȡog.de' => 'ȡog.de',
            '☕.de' => '☕.de',
            'I♥NY.de' => 'i♥ny.de',
            'ＡＢＣ・日本.co.jp' => 'abc・日本.co.jp',
            '日本｡co｡jp' => '日本.co.jp',
            '日本｡co．jp' => '日本.co.jp',
            'x\u0327\u0301.de' => 'x̧́.de',
            'x\u0301\u0327.de' => 'x̧́.de',
            'σόλος.gr' => 'σόλος.gr',
            'Σόλος.gr' => 'σόλος.gr',
            'ΣΌΛΟΣ.grﻋﺮﺑﻲ.de' => 'σόλοσ.grعربي.de',
            'عربي.de' => 'عربي.de',
            'نامهای.de' => 'نامهای.de',
            'نامه\u200Cای.de' => 'نامه‌ای.de',
        ],
    ];

    public function testConvert()
    {
        $result = IDNATranslator::convert('münchen.de');
        $this->assertEquals(['IDN' => 'münchen.de', 'PUNYCODE' => 'xn--mnchen-3ya.de'], $result);

        $result = IDNATranslator::convert('xn--mnchen-3ya.de');
        $this->assertEquals(['IDN' => 'münchen.de', 'PUNYCODE' => 'xn--mnchen-3ya.de'], $result);

        $result = IDNATranslator::convert('🌐.ws');
        $this->assertEquals(['IDN' => '🌐.ws', 'PUNYCODE' => 'xn--wg8h.ws'], $result);

        $result = IDNATranslator::convert('xn--wg8h.ws');
        $this->assertEquals(['IDN' => '🌐.ws', 'PUNYCODE' => 'xn--wg8h.ws'], $result);
    }

    public function testConvertBulk()
    {
        // Define an array of domain names to test
        $domains = [
            'münchen.de',
            'xn--mnchen-3ya.de',
            '🌐.ws',
            'xn--wg8h.ws',
            '😊.com',
            'xn--o28h.com',
            '🎉.net',
            'xn--dk8h.net'
        ];

        // Call the convertBulk method
        $convertedDomains = IDNATranslator::convert($domains);

        // Check if the returned array has the correct keys
        $this->assertArrayHasKey('münchen.de', $convertedDomains);
        $this->assertArrayHasKey('xn--mnchen-3ya.de', $convertedDomains);
        $this->assertArrayHasKey('🌐.ws', $convertedDomains);
        $this->assertArrayHasKey('xn--wg8h.ws', $convertedDomains);
        $this->assertArrayHasKey('😊.com', $convertedDomains);
        $this->assertArrayHasKey('xn--o28h.com', $convertedDomains);
        $this->assertArrayHasKey('🎉.net', $convertedDomains);
        $this->assertArrayHasKey('xn--dk8h.net', $convertedDomains);

        // Check if the converted domains have the correct values
        $this->assertEquals(['IDN' => 'münchen.de', 'PUNYCODE' => 'xn--mnchen-3ya.de'], $convertedDomains['münchen.de']);
        $this->assertEquals(['IDN' => 'münchen.de', 'PUNYCODE' => 'xn--mnchen-3ya.de'], $convertedDomains['xn--mnchen-3ya.de']);
        $this->assertEquals(['IDN' => '🌐.ws', 'PUNYCODE' => 'xn--wg8h.ws'], $convertedDomains['🌐.ws']);
        $this->assertEquals(['IDN' => '🌐.ws', 'PUNYCODE' => 'xn--wg8h.ws'], $convertedDomains['xn--wg8h.ws']);
        $this->assertEquals(['IDN' => '😊.com', 'PUNYCODE' => 'xn--o28h.com'], $convertedDomains['😊.com']);
        $this->assertEquals(['IDN' => '😊.com', 'PUNYCODE' => 'xn--o28h.com'], $convertedDomains['xn--o28h.com']);
        $this->assertEquals(['IDN' => '🎉.net', 'PUNYCODE' => 'xn--dk8h.net'], $convertedDomains['🎉.net']);
        $this->assertEquals(['IDN' => '🎉.net', 'PUNYCODE' => 'xn--dk8h.net'], $convertedDomains['xn--dk8h.net']);
    }

    // Test cases for conversion from IDN to Punycode
    public function testIdnToPunycodeConversion()
    {
        foreach (self::$data['convert'] as $idn => $punycode) {
            $this->assertEquals($punycode, IDNATranslator::convert($idn)['PUNYCODE']);
        }
    }

    // Test cases for converting domain names to Punycode
    public function testToASCII()
    {
        foreach (self::$data['toAscii'] as $input => $output) {
            $this->assertEquals($output, IDNATranslator::toASCII(
                $input,
                ["transitionalProcessing" => true]
            ));
        }
    }

    // Test cases for converting Transitional domain names to Punycode
    public function testToASCIIAlwaysWithTransitional()
    {
        foreach (self::$data['toAsciiWithTransitional'] as $input => $output) {
            $withTransition = IDNATranslator::toASCII(
                $input,
                ["transitionalProcessing" => true]
            );
            $withoutTransition = IDNATranslator::toASCII(
                $input,
                ["transitionalProcessing" => false]
            );
            $this->assertEquals(
                $output,
                $withTransition,
                "\nInput: {$input}\nWith transition: " . $withTransition . "\nWithout transition: " . $withoutTransition
            );
        }
    }

    // Test cases for converting Transitional domain names to Punycode
    public function testToASCIIAlwaysWithoutTransitional()
    {
        foreach (self::$data['toAsciiWithoutTransitional'] as $input => $output) {
            $withTransition = IDNATranslator::toASCII(
                $input,
                ["transitionalProcessing" => true]
            );
            $withoutTransition = IDNATranslator::toASCII(
                $input,
                ["transitionalProcessing" => false]
            );
            $this->assertEquals(
                $output,
                $withoutTransition,
                "\nInput: {$input}\nWith transition: " . $withTransition . "\nWithout transition: " . $withoutTransition
            );
        }
    }

    // Test cases for converting domain names to Unicode
    public function testToUnicode()
    {
        foreach (self::$data['toUnicode'] as $input => $output) {
            $this->assertEquals($output, IDNATranslator::toUnicode($input, ["transitionalProcessing" => true]), "{$input} : {$output}");
        }
    }
}

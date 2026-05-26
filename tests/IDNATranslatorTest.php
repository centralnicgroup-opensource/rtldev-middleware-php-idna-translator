<?php

namespace CNIC\IDNA\Tests;

use PHPUnit\Framework\TestCase;
use CNIC\IDNA\Factory\ConverterFactory;

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
            'ΣΌΛΟΣ.grﻋﺮﺑﻲ.de' => 'xn--wxaijb9b.xn--gr-gtd9a1b0g.de',
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
            'Öbb.at' => 'öbb.at',
            'ÖBB.at' => 'öbb.at',
            'O\u0308bb.at' => 'öbb.at',
            'xn--bb-eka.at' => 'öbb.at',
            'faß.de' => 'faß.de',
            'fass.de' => 'fass.de',
            'xn--fa-hia.de' => 'faß.de',
            'not=std3' => 'not=std3',
            '\ud83d\udca9' => '💩',
            '\ud87e\udcca' => '𣀊',
            '\udb40\udd00\ud87e\udcca' => '𣀊',
            //'\ud83d\udca9' => '\ud83d\udca9',
            //'\ud87e\udcca' => '\ud84c\udc0a',
            //'\udb40\udd00\ud87e\udcca' => '\ud84c\udc0a',
            'fäß.de' => 'fäß.de',
            '₹.com' => '₹.com',
            '𑀓.com' => '𑀓.com',
            //'a‌b' => 'a\u200Cb',
            'a‌b' => 'a‌b',
            'xn--ab-j1t' => 'a‌b',
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
            'ΣΌΛΟΣ.grﻋﺮﺑﻲ.de' => 'σόλος.grعربي.de',
            'عربي.de' => 'عربي.de',
            'نامهای.de' => 'نامهای.de',
            'نامه\u200Cای.de' => 'نامه‌ای.de',
        ],
    ];

    public function testConvert()
    {
        $result = ConverterFactory::convert('münchen.de');
        $this->assertEquals(['idn' => 'münchen.de', 'punycode' => 'xn--mnchen-3ya.de'], $result);

        $result = ConverterFactory::convert('xn--mnchen-3ya.de');
        $this->assertEquals(['idn' => 'münchen.de', 'punycode' => 'xn--mnchen-3ya.de'], $result);

        $result = ConverterFactory::convert('🌐.ws');
        $this->assertEquals(['idn' => '🌐.ws', 'punycode' => 'xn--wg8h.ws'], $result);

        $result = ConverterFactory::convert('xn--wg8h.ws');
        $this->assertEquals(['idn' => '🌐.ws', 'punycode' => 'xn--wg8h.ws'], $result);
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
        $convertedDomains = ConverterFactory::convert($domains);

        // Check if the converted domains have the correct values
        $this->assertEquals(['idn' => 'münchen.de', 'punycode' => 'xn--mnchen-3ya.de'], $convertedDomains[0]);
        $this->assertEquals(['idn' => 'münchen.de', 'punycode' => 'xn--mnchen-3ya.de'], $convertedDomains[1]);
        $this->assertEquals(['idn' => '🌐.ws', 'punycode' => 'xn--wg8h.ws'], $convertedDomains[2]);
        $this->assertEquals(['idn' => '🌐.ws', 'punycode' => 'xn--wg8h.ws'], $convertedDomains[3]);
        $this->assertEquals(['idn' => '😊.com', 'punycode' => 'xn--o28h.com'], $convertedDomains[4]);
        $this->assertEquals(['idn' => '😊.com', 'punycode' => 'xn--o28h.com'], $convertedDomains[5]);
        $this->assertEquals(['idn' => '🎉.net', 'punycode' => 'xn--dk8h.net'], $convertedDomains[6]);
        $this->assertEquals(['idn' => '🎉.net', 'punycode' => 'xn--dk8h.net'], $convertedDomains[7]);
    }

    // Test cases for conversion from IDN to Punycode
    public function testidnToPunycodeConversion()
    {
        foreach (self::$data['convert'] as $idn => $punycode) {
            $this->assertEquals($punycode, ConverterFactory::convert($idn)['punycode']);
        }
    }

    // Test cases for converting domain names to Punycode
    public function testToASCII()
    {
        foreach (self::$data['toAscii'] as $input => $output) {
            $this->assertEquals($output, ConverterFactory::toASCII(
                $input,
                ["transitionalProcessing" => true]
            ));
        }
    }

    // Test cases for converting Transitional domain names to Punycode
    public function testToASCIIWithTransitional()
    {
        foreach (self::$data['toAsciiWithTransitional'] as $input => $output) {
            $withTransition = ConverterFactory::toASCII(
                $input,
                ["transitionalProcessing" => true]
            );
            $withoutTransition = ConverterFactory::toASCII(
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
    public function testToASCIIWithoutTransitional()
    {
        foreach (self::$data['toAsciiWithoutTransitional'] as $input => $output) {
            $withTransition = ConverterFactory::toASCII(
                $input,
                ["transitionalProcessing" => true]
            );
            $withoutTransition = ConverterFactory::toASCII(
                $input,
                ["transitionalProcessing" => false]
            );
            $this->assertEquals(
                $output,
                $withoutTransition,
                "\nInput: {$input}
                \nWith transition: " . $withTransition . "
                \nWithout transition: " . $withoutTransition
            );
        }
    }

    public function testTransitionalProcessingAutoDetection()
    {
        $nonTransitionalDomains = [
            'example.art',
            'example.be',
            'example.ca',
            'example.de',
            'EXAMPLE.FR',
            'example.pm',
            'example.re',
            'example.swiss',
            'example.tf',
            'example.wf',
            'example.yt',
            'example.de.',
        ];

        foreach ($nonTransitionalDomains as $domain) {
            $this->assertTrue(ConverterFactory::transitionalProcessing($domain), $domain);
        }

        $transitionalDomains = [
            'example.com',
            'example.de.com',
            'ääkköstestitilaus.online',
            'ääkköstestitilaus.store',
            'ääkköstestitilaus.site',
        ];

        foreach ($transitionalDomains as $domain) {
            $this->assertFalse(ConverterFactory::transitionalProcessing($domain), $domain);
        }

        $this->assertTrue(ConverterFactory::transitionalProcessing('münchen', ['domain' => 'münchen.de']));
    }

    // Test cases for converting domain names to Unicode
    public function testToUnicode()
    {
        foreach (self::$data['toUnicode'] as $input => $output) {
            $this->assertEquals(
                $output,
                ConverterFactory::toUnicode($input, ["transitionalProcessing" => true]),
                "{$input} : {$output}"
            );
        }
    }
}

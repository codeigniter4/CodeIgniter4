<?php

namespace CodeIgniter\Mailer;

use CodeIgniter\Test\CIUnitTestCase;

class EncodeTest extends CIUnitTestCase
{
    public function qEncodingProvider()
    {
        return [
            'Encode for text; char encoding default (iso88591)' => [
                'input'    => "\xa1Hola! Se\xf1or!",
                'expected' => '=A1Hola!_Se=F1or!',
                'position' => 'text',
            ],
            'Encode for TEXT (uppercase); char encoding default (iso88591)' => [
                'input'    => "\xa1Hola! Se\xf1or!",
                'expected' => '=A1Hola!_Se=F1or!',
                'position' => 'TEXT',
            ],
            'Encode for comment; char encoding default (iso88591)' => [
                'input'    => "\xa1Hola! Se\xf1or!",
                'expected' => '=A1Hola!_Se=F1or!',
                'position' => 'comment',
            ],
            'Encode for Phrase (mixed case); char encoding default (iso88591)' => [
                'input'    => "\xa1Hola! Se\xf1or!",
                'expected' => '=A1Hola!_Se=F1or!',
                'position' => 'Phrase',
            ],
            'Encode for text; char encoding explicit: utf-8' => [
                'input'    => "\xc2\xa1Hola! Se\xc3\xb1or!",
                'expected' => '=C2=A1Hola!_Se=C3=B1or!',
                'position' => 'text',
                'charset'  => 'utf-8',
            ],
            'Encode for text; char encoding explicit: utf-8; string contains "=" character' => [
                'input'    => "Nov\xc3\xa1=",
                'expected' => 'Nov=C3=A1=3D',
                'position' => 'text',
                'charset'  => 'utf-8',
            ],
            'Encode for text; char encoding default (iso88591); string containing new lines' => [
                'input'    => "\xa1Hola!\r\nSe\xf1or!\r\n",
                'expected' => '=A1Hola!Se=F1or!',
                'position' => 'text',
            ],
            'Encode for text; char encoding explicit: utf-8; phrase vs text regex (text)' => [
                'input'    => "Hello?\xbdWorld\x5e\xa9",
                'expected' => 'Hello=3F=BDWorld^=A9',
                'position' => 'text',
                'charset'  => 'UTF-8',
            ],
        ];
    }

    /**
     * @dataProvider qEncodingProvider
     *
     * @param string $input    The text to encode.
     * @param string $expected The expected function return value.
     * @param string $charset  Optional. The charset to use.
     */
    public function testQEncode(string $input, string $expected, ?string $position = null, ?string $charset = null)
    {
        $config = config('Mailer');
        $config->charset = ! empty($charset) ? $charset : $config->charset;
        $encoder = new Encode($config);

        $result = $encoder->Q($input);

        self::assertSame($expected, $result);
    }

    public function testQuotedPrintable()
    {
        $encoder = new Encode(config('Mailer'));
        $string = 'Möchten Sie ein paar Äpfel?';
        $expected = "M=C3=B6chten Sie ein paar =C3=84pfel?";

        $this->assertEquals($expected, $encoder->quotedPrintable($string));
    }
}

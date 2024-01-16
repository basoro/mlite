<?php

use LZCompressor\LZString as LZString;

class TestLZStringUTF8 extends PHPUnit\Framework\TestCase
{

    /**
     * @dataProvider simpleTextProvider
     * @param $test
     * @throws Exception
     */
    public function testUTF8_1Byte($test) {
        $this->assertEquals($test, LZString::decompress(LZString::compress($test)));
    }

    /**
     * @dataProvider twoBytesProvider
     * @param $test
     * @throws Exception
     */
    public function testUTF8_2Bytes($test) {
        $this->assertEquals($test, LZString::decompress(LZString::compress($test)));
    }

    /**
     * @dataProvider threeBytesProvider
     * @param $test
     * @throws Exception
     */
    public function testUTF8_3Bytes($test) {
        $this->assertEquals($test, LZString::decompress(LZString::compress($test)));
    }

    /**
     * @dataProvider compressed64Provider
     * @param $input
     * @param $expected
     */
    public function testDecompressFromBase64($input, $expected) {
        $this->assertEquals($expected, LZString::decompressFromBase64($input));
    }

    public function simpleTextProvider() {
        return [
            ['a'],
            ['A'],
            ['Aa'],
            ['AA'],
            ['ӪӹĆĹ߅œƠيϼϾ'],
            ['Ӫӹ'],
            ['AAAAAA']
        ];
    }

    public function oneByteProvider() {
        return $this->byteProvider(1, 1000, 1000);
    }

    public function twoBytesProvider() {
        return $this->byteProvider(2, 500, 500);
    }

    public function threeBytesProvider() {
        return $this->byteProvider(3, 500, 100);
    }

    private function byteProvider($byteCnt, $count, $length) {
        $domain = array(
            1 => array(0, 127),
            2 => array(128, 2014),
            3 => array(2048, 65535)
        );
        $start = $domain[$byteCnt][0];
        $end = $domain[$byteCnt][1];
        $testCases = [];
        $rands = [];
        for($i=0; $i<$count; $i++) {
            $test = '';
            for($j=0; $j<$length; $j++) {
                $rand = rand($start, $end);
                $rands[] = $rand;
                $test .= \LZCompressor\LZUtil::utf8_chr($rand);
            }
            $testCases[] = array($test);
        }
        return $testCases;
    }

    public function compressed64Provider() {
        return [
            ['BISwNABA7gFghgFwAYGcIFcAOBCIA===', 'Hi, what`s up!'],
            ['BpA=', 'X']
        ];
    }
}

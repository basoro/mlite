<?php

use LZCompressor\LZString as LZString;

class TestLZString extends PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider simpleTextProvider
     * @param $test
     * @throws Exception
     */
    public function testSimple($test) {
        $this->assertEquals($test, LZString::decompress(LZString::compress($test)));
    }

    public function simpleTextProvider() {
        return [
            ['a'],
            ['A'],
            ['Aa'],
            ['AA'],
            ['ӪӹĆĹ߅œƠيϼϾ'],
            ['Ӫӹ'],
            ['AAAAAA'],
            ['متن شگفت انگیز در اینجا'],
            [" «sauvegardes», «"]
        ];
    }
}

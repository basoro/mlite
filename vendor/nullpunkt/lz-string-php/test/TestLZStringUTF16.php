<?php

use LZCompressor\LZString as LZString;

class TestLZStringUTF16 extends PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider simpleTextProvider
     * @param $test
     * @throws Exception
     */
    public function testSimple($test) {
        $this->assertEquals($test, LZString::decompress(LZString::compress($test)));
    }

    /**
     * @dataProvider simpleTextProvider
     * @param $test
     * @throws Exception
     */
    public function testCompressDecompress64($test) {
        $this->assertEquals($test, LZString::decompressFromUTF16(LZString::compressToUTF16($test)));
    }

    public function simpleTextProvider() {
        return [
            ["Définir les permissions d'écriture (757 ou 775 selon votre système) sur les «sauvegardes», lib',' plugins ',' test 'et les dossiers' tmp '"],
            ['Tämä osoittaa merkkejä joidenkin aksentti :ä, ö, å'],
            ['а б в г д е ё ж з и й к л м н о п р с т у ф х ц ч ш щ ъ ы ь э ю я'],
            ['馜簣襃 䰩かクェぢょ菣 ぽ饧饦褎姌 蟨姚獣禺蛣'],
            ['متن شگفت انگیز دینجا'],
            ['+-×º=()<>%'],
            ['x≜≈∑∏e'],
            ['∡∟º´|||Δ'],
            ['(x)μσρzxχ 2'],
            ['{ }∩∪⊂∈Øℝ'],
            ["εiy'∫d/dx"],
            ['αβγδεζηθ'],
            ['XIVLCD']
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
}

<?php
namespace LZCompressor;

class LZData
{
    /**
     * @var
     */
    public $str = '';

    /**
     * @var
     */
    public $val;

    /**
     * @var int
     */
    public $position = 0;

    /**
     * @var int - index of letters (may be multiple of characters)
     */
    public $index = 1;
    
    /*
     * @var bool - set to true if theindex is out of str range
     */
    public $end = true;
    
    /**
     * @param unknown $str
     */
    public function append($str) {
        $this->str .= $str;
    }
}

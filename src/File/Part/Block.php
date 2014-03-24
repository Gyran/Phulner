<?php
namespace Phulner\File\Part;

use Phulner\File\PartAbstract;

class Block extends PartAbstract {
    public function __construct($lines) {
        $this->_lines = $lines;
    }

    public function toString () {
        return $this->_lines;
    }

    private $_lines;
}

?>

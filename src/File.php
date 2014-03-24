<?php
namespace Phulner;

use Phulner\File\PartAbstract;

class File implements \IteratorAggregate {
    public function __construct ($path) {
        $this->_path = $path;
        $this->_parts = [];
    }

    public function getPath () {
        return $this->_path;
    }

    public function addPart (PartAbstract $part) {
        $this->_parts[] = $part;
    }

    public function &getParts () {
        return $this->_parts;
    }

    public function toString () {
        $string = "";
        foreach ($this->_parts as $part) {
            $string = $string . $part->toString();
        }

        return $string;
    }

    public function getIterator () {
        return new \ArrayIterator($this->_parts);
    }

    private $_path;
    private $_parts;
}

?>

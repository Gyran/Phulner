<?php
namespace Phulner\NodeVisitor\Scope\Variable;

use Phulner\NodeVisitor\Scope\Variable;

class Array_ extends Variable {
    public function __construct($name, $taint = [], $inherit = false, $keys = []) {
        parent::__construct($name, $taint);
        $this->_inherit = $inherit;
        $this->_keys = $keys;
    }

    public function addKey ($key, Variable $var) {
        $this->_keys[$key] = $var;
    }

    public function getKey ($key) {
        if (isset($this->_keys[$key])) {
            return $this->_keys[$key];
        }
        // $key does not exist create it!
        $var = new Array_($key, $this->getTaint(), true);
        $this->addKey($key, $var);

        return $var;

        //throw new \OutOfBoundsException(sprintf("Key [%s] could not be accessed", $key));
    }

    public function getTaint () {
        if ($this->_inherit) {
            return parent::getTaint();
        }
        return [];
    }

    protected $_keys;
    protected $_inherit;
}

?>

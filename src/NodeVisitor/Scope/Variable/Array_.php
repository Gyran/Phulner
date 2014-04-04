<?php
namespace Phulner\NodeVisitor\Scope\Variable;

use Phulner\NodeVisitor\Scope\VariableAbstract;

class Array_ extends VariableAbstract {
    public function __construct($name, $taint = [], $inherit = false, $keys = []) {
        parent::__construct($name, $taint);
        $this->_inherit = $inherit;
        $this->_keys = $keys;
    }

    public function hasKey ($key) {
        return isset($this->_keys[$key]);
    }

    public function addKey (VariableAbstract &$var) {
        $this->_keys[$var->getName()] = $var;
    }

    public function &getKey ($key) {
        $var = &$this->_keys[$key];
        return $var;
    }

    public function getTaint () {
        if ($this->_inherit) {
            return parent::getTaint();
        }
        return [];
    }

    public function getKeys () {
        return $this->_keys;
    }

    public function getInherit () {
        return $this->_inherit;
    }

    protected $_keys;
    protected $_inherit;
}
?>

<?php
namespace Phulner\NodeVisitor\Scope\Variable;

use Phulner\NodeVisitor\Scope\VariableAbstract;

class Variable extends VariableAbstract {
    public function __construct($name, $taint = []) {
        parent::__construct($name, $taint);
        $this->_valueSet = false;
    }

    public function setValue ($value) {
        $this->_valueSet = true;
        $this->_value = $value;
    }

    public function getValue () {
        return $this->_value;
    }

    public function getValueSet () {
        return $this->_valueSet;
    }

    protected $_value;
    protected $_valueSet;
}


?>

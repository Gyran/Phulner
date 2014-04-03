<?php
namespace Phulner\NodeVisitor\Scope;

abstract class VariableAbstract {
    public function __construct($name, $taint = []) {
        $this->_name = $name;
        $this->_taint = $taint;
    }

    public function getType () {
        return get_class($this);
    }

    public function getName () {
        return $this->_name;
    }

    public function setName ($name) {
        $this->_name = $name;
    }

    public function getTaint () {
        return $this->_taint;
    }

    public function addTaint ($taint) {
        $this->_taint[] = $taint;
    }

    public function setTaint ($taint) {
        $this->_taint = $taint;
    }

    protected $_taint;
    protected $_name;
}

?>

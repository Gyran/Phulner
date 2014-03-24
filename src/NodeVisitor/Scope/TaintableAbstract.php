<?php
namespace Phulner\NodeVisitor\Scope;

abstract class TaintableAbstract implements Taintable {
    public function getName () {
        return $this->_name;
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

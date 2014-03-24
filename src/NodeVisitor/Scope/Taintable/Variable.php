<?php
namespace Phulner\NodeVisitor\Scope\Taintable;

use Phulner\NodeVisitor\Scope\TaintableAbstract;

class Variable extends TaintableAbstract {
    public function __construct($name, $taint) {
        $this->_name = $name;
        $this->_taint = $taint;
    }
}

?>

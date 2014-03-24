<?php
namespace Phulner\NodeVisitor\Scope\Taintable;

use Phulner\NodeVisitor\Scope\TaintableAbstract;

class Array_ extends TaintableAbstract {
    public function __construct($name, $taint, $inherith, $keys) {
        $this->_name = $name;
        $this->_taint = $taint;
        $this->_inherith = $inherith;
        $this->_keys = $keys;
    }

    protected $_keys;
    protected $_inherith;
}

?>

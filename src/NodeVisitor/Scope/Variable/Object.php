<?php
namespace Phulner\NodeVisitor\Scope\Variable;

use Phulner\NodeVisitor\Scope\Variable;

class Object extends Variable {
    public function __construct($name, $taint = [], $inherith = false, $properties = []) {
        parent::__construct($name, $taint);
        $this->_inherith = $inherith;
        $this->_properties = $properties;
    }

    protected $_properties;
    protected $_inherith;
}

?>

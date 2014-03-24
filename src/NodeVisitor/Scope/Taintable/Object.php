<?php
namespace Phulner\NodeVisitor\Scope\Taintable;

use Phulner\NodeVisitor\Scope\TaintableAbstract;

class Object extends TaintableAbstract {
    public function __construct($name) {
        parent::__construct($name);
        $protected = [];
    }

    protected $properties;
    protected $inherith;
}

?>

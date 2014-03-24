<?php
namespace Phulner\NodeVisitor\Scope;

interface Taintable {
    public function getName ();
    public function getTaint ();
    public function addTaint ($taint);
    public function setTaint ($taint);
}

<?php
namespace Phulner\PhpParser;

class PrettyPrinter extends \PhpParser\PrettyPrinter\Standard {
    public function pScalar_String(\PHPParser\Node\Scalar\String $node) {
        return $this->pNoIndent($node->getAttribute('originalValue'));
    }
}

?>

<?php
namespace Phulner\PhpParser;

use Phulner\NodeVisitor\Scope\Variable;

class NodeDumper extends \PhpParser\NodeDumper {
    public function dump($node) {
        $r = "";

        if ($node instanceof Variable\Variable) {
            $r .= $node->getType() . "(";

            $r .= "\n    name: " . $node->getName();
            $r .= "\n    taint: ";
            $r .= str_replace("\n", "\n    ", parent::dump($node->getTaint()));
            if ($node->getValueSet()) {
                $r .= "\n    value: " . $node->getValue();
            }
        } elseif ($node instanceof Variable\Array_) {
            $r .= "\n    name: " . $node->getName();
            $r .= "\n    inherit: ";
            if ($node->getInherit()) {
                $r .= "true";
            } else {
                $r .= "false";
            }
            $r .= "\n    taint: ";
            $r .= str_replace("\n", "\n    ", parent::dump($node->getTaint()));
        } else {
            try {
                $r = parent::dump($node);
            } catch (\Exception $e) {

            }
        }

        return $r;
    }
}

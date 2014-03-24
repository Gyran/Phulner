<?php
namespace Phulner;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\ConstFetch;

class Function_ {
    static public function bitwiseOrContainsConst(Expr $node, $constName) {
        if ($node instanceof BitwiseOr) {
            if (self::bitwiseOrContainsConst($node->left, $constName) ||
                self::bitwiseOrContainsConst($node->right, $constName)) {
                return true;
            }
        } elseif ($node instanceof ConstFetch) {
            return ($node->name->toString() === $constName);
        }
        return false;
    }
}



?>

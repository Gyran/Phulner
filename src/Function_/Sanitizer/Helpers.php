<?php
namespace Phulner\Function_\Sanitizer;

use PhpParser\Node;

class Helpers {
    static public function bitwiseOrContainsConst(Node\Expr $node, $constName) {
        if ($node instanceof Node\Expr\BinaryOp\BitwiseOr) {
            if (self::bitwiseOrContainsConst($node->left, $constName) ||
                self::bitwiseOrContainsConst($node->right, $constName)) {
                return true;
            }
        } elseif ($node instanceof Node\Expr\ConstFetch) {
            return ($node->name->toString() === $constName);
        }
        return false;
    }

    /* Commonly used functions in handlers */
    // can
    static public function can_always () {
        return function (Node\Expr\FuncCall $funcCall, $options) {
            return true;
        };
    }

    //// returns true if $options->sanitation === $sanitation
    public function can_sanitation ($sanitation) {
        return function (Node\Expr\FuncCall $funcCall, $options) use ($sanitation) {
            if ($options->sanitation === $sanitation) {
                return true;
            }
            return false;
        };
    }

    // return
    //// returns taint from argument number $num
    public function return_taintFromArgument($num) {
        return function (Node\Expr\FuncCall $funcCall, $options) use ($num) {
            return $funcCall->args[$num]->taint;
        };
    }

    //// returns the taint $taint
    public function return_taint($taint) {
        return function (Node\Expr\FuncCall $funcCall, $options) use ($taint) {
            return $taint;
        };
    }

    //// returns no taint
    public function return_noTaint () {
        return self::return_taint([]);
    }

    // replace
    //// returns the argument number $num
    public function replace_returnArgument ($num) {
        return function (Node\Expr\FuncCall $funcCall, $options) use ($num) {
            return $funcCall->args[$num]->value;
        };
    }

    //// ändra
    public function replace_htmlspecialchars ($argument) {
        $name = new Node\Name("htmlspecialchars");
        $funcCall = new Node\Expr\FuncCall($name, [$argument]);
        return $funcCall;
    }

    // input tainted
    //// retuns true if argument number $num has taint
    public function input_argument ($num) {
        return function (Node\Expr\FuncCall $funcCall, $options) use ($num) {
            return !empty($funcCall->args[$num]->taint);
        };
    }


}


?>

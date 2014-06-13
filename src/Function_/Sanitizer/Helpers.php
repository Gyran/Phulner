<?php
namespace Phulner\Function_\Sanitizer;
use Phulner\Function_\Sanitizer\ReturnCallableHandler;
use PhpParser\Node\Expr\FuncCall;

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

    static public function variable ($name) {
        return new Node\Expr\Variable($name);
    }

    static public function arrayDimFetch ($var, $dim) {
        return new Node\Expr\ArrayDimFetch($var, $dim);
    }

    /* Commonly used functions in handlers */
    // can
    static public function can_always () {
        return function (Node\Expr\FuncCall $funcCall, $options) {
            return true;
        };
    }

    public function can_input ($input) {
        return function (Node\Expr\FuncCall $funcCall, $options) use ($input) {
            if ($options->input === $input) {
                return true;
            }
            return false;
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

    //// returns true if $options->sanitation === $sanitation && $options->output === $output
    public function can_sanitation_output ($sanitation, $output) {
        return function (Node\Expr\FuncCall $funcCall, $options) use ($sanitation, $output) {
            if ($options->sanitation === $sanitation && $options->output === $output) {
                return true;
            }
            return false;
        };
    }

    static public function can_sanitation_outputs ($sanitation, $outputs) {
        return function (Node\Expr\FuncCall $funcCall, $options) use ($sanitation, $outputs) {
            if ($options->sanitation === $sanitation && in_array($options->output, $outputs)) {
                return true;
            }
            return false;
        };
    }

    //// returns true if $options->output in array $outputs
    public function can_outputs ($outputs) {
        return function (Node\Expr\FuncCall $funcCall, $options) use ($outputs) {
            if (in_array($options->output, $outputs)) {
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

    static public function return_handler_always_noTaint () {
        $can = Helpers::can_always();
        $do = Helpers::return_noTaint();
        return new ReturnCallableHandler($can, $do);
    }

    static public function return_handler_always_argumentTaint ($argument) {
        $can = Helpers::can_always();
        $do = Helpers::return_taintFromArgument($argument);
        return new ReturnCallableHandler($can, $do);
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

    static public function replace_function_arguments ($function, $arguments) {
        $name = new Node\Name($function);
        $funcCall = new Node\Expr\FuncCall($name, $arguments);
        return $funcCall;
    }

    //// returns blacklist filter
    static public function replace_blacklist ($blacklist, $subject) {
        $name = new Node\Name("preg_replace");
        $regex = new Node\Scalar\String($blacklist, ["originalValue" => '"' . $blacklist . '"']);
        $empty = new Node\Scalar\String("", ["originalValue" => '""']);

        $args = [
            new Node\Arg($regex),
            new Node\Arg($empty),
            $subject
        ];

        $funcCall = new Node\Expr\FuncCall($name, $args);
        return $funcCall;
    }

    static public function replace_preg_replace ($replace, $subject) {
        $name = new Node\Name("preg_replace");
        $regex = new Node\Scalar\String($replace, ["originalValue" => '"' . $replace . '"']);
        $empty = new Node\Scalar\String("", ["originalValue" => '""']);

        $args = [
            new Node\Arg($regex),
            new Node\Arg($empty),
            $subject
        ];

        $funcCall = new Node\Expr\FuncCall($name, $args);
        return $funcCall;
    }

    //********** replace none ***********/
    static public function replace_handler_none_returnArgument ($argument) {
        $can = Helpers::can_sanitation("NONE");
        $do = Helpers::replace_returnArgument($argument);

        return new ReturnCallableHandler($can, $do);
    }

    //********** replace blacklist ***********/
    // replace chain for sanitation BLACKLIST output NONJS_DOUBLEQUOTES
    static public function replace_handler_blacklist_nonjs_doublequotes () {
        $can = Helpers::can_sanitation_output("BLACKLIST", "NONJS_DOUBLEQUOTES");
        $do = function (FuncCall $funcCall, $options) {
            return Helpers::replace_preg_replace('/[<>\(\)\: ]/', $funcCall->args[0]);
        };
        $handler = new ReturnCallableHandler($can, $do);

        return $handler;
    }

    // replace chain for sanitation BLACKLIST output NONJS_DOUBLEQUOTES
    static public function replace_handler_blacklist_normal_tag ($argument) {
        $can = Helpers::can_sanitation_output("BLACKLIST", "NORMAL_TAG");
        $do = function (FuncCall $funcCall, $options) use ($argument) {
            return Helpers::replace_preg_replace('/(<script.+?<\/script>)/i', $funcCall->args[$argument]);
        };

        return new ReturnCallableHandler($can, $do);
    }

    static public function replace_handler_blacklist_url_all ($argument) {
        $can = Helpers::can_sanitation_outputs("BLACKLIST", ["URL_NOQUOTES", "URL_SINGLEQUOTES", "URL_DOUBLEQUOTES"]);
        $do = function (FuncCall $funcCall, $options) use ($argument) {
            return Helpers::replace_preg_replace('/(javascript|[<>\\\'\\" \(\)])/i', $funcCall->args[$argument]);
        };

        return new ReturnCallableHandler($can, $do);
    }

    //********** replace whitelist ***********/
    // data:text/html;base64,PHNjcmlwdD5hbGVydCgnWFNTJyk8L3NjcmlwdD4K
    static public function replace_handler_whitelist_url_all ($argument) {
        $can = Helpers::can_sanitation_outputs("WHITELIST", ["URL_NOQUOTES", "URL_SINGLEQUOTES", "URL_DOUBLEQUOTES"]);
        $do = function (FuncCall $funcCall, $options) use ($argument) {
            return Helpers::replace_preg_replace('/[^!#$&-;=?-[]_a-z~A-Za-z%]/', $funcCall->args[$argument]);
        };

        return new ReturnCallableHandler($can, $do);
    }

    //********** replace encoding ***********/
    static public function replace_handler_encoding_nonjs_singlequotes ($argument) {
        $can = Helpers::can_sanitation_output("ENCODING", "URL_SINGLEQUOTES");
        $do = function (FuncCall $funcCall, $options) use ($argument) {
            return Helpers::replace_function_arguments("htmlspecialchars", [$funcCall->args[$argument]]);
        };

        return new ReturnCallableHandler($can, $do);
    }

    // input tainted
    static public function input_handler_always_argument ($argument) {
        $can = Helpers::can_always();
        $do = Helpers::input_argument($argument);
        return new ReturnCallableHandler($can, $do);
    }

    static public function input_handler_always_taint ($taint) {
        $can = Helpers::can_always();
        $do = Helpers::return_taint($taint);
        return new ReturnCallableHandler($can, $do);
    }

    //// retuns true if argument number $num has taint
    public function input_argument ($num) {
        return function (Node\Expr\FuncCall $funcCall, $options) use ($num) {
            return !empty($funcCall->args[$num]->taint);
        };
    }

}


?>

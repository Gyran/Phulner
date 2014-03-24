<?php
namespace Phulner\Function_\Sanitizer;

use Phulner\Function_;
use Phulner\Function_\Sanitizer;
use Phulner\Function_\Sanitizer\ReturnCallableHandler;

use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;

class Factory {
    public function add (Sanitizer $function) {
        $this->_functions[$function->getName()] = $function;
    }

    public function &get ($function) {
        if (isset($this->_functions[$function])) {
            return $this->_functions[$function];
        }
        $method = "_function_" . $function;
        if (method_exists($this, $method)) {
            $this->add($this->$method());
            return $this->get($function);
        }
        return null;
    }

    public function exists ($function) {
        if (isset($this->_functions[$function])) {
            return true;
        }
        $method = "_function_" . $function;
        if (method_exists($this, $method)) {
            return true;
        }
        return false;
    }

    private function _createReturnCallableHandler (callable $can, callable $do) {
        return new ReturnCallableHandler($can, $do);
    }

    private function _function_intval () {
        $function = new Sanitizer("intval");

        // returned taint chain
        {
            $can = $this->handlers_can_always();
            $do = $this->handlers_return_noTaint();
            $handler = new ReturnCallableHandler($can, $do);
            $function->addReturnedTaintHandler($handler);
        }

        // input tainted chain
        {
            $can = $this->handlers_can_always();
            $do = $this->handlers_input_argument(0);
            $handler = new ReturnCallableHandler($can, $do);
            $function->addTaintedInputHandler($handler);
        }

        // replace chain
        {
            // sanitation === NONE
            {
                $can = $this->handlers_can_sanitation("NONE");
                $do = $this->handlers_replace_returnArgument(0);
                $handler = new ReturnCallableHandler($can, $do);
                $function->addReplaceHandler($handler);
            }
            { // sanitation === INSUFFICIENT_ENCODING
                // make sure htmlspecialchars is called without ENT_QUOTES
                $can = $this->handlers_can_sanitation("INSUFFICIENT_ENCODING");
                $do = function (FuncCall $funcCall, $options) {
                    return $this->handlers_replace_htmlspecialchars($funcCall->args[0]);
                };
                $handler = new ReturnCallableHandler($can, $do);
                $function->addReplaceHandler($handler);
            }
        }

        return $function;
    }

    private function _function_htmlspecialchars () {
        $function = new Sanitizer("htmlspecialchars");
        // returned taint chain
        {
            {
                $can = $this->handlers_can_always();
                $do = function (FuncCall $funcCall, $options) {
                    if (isset($funcCall->args[1]) &&
                        Function_::bitwiseOrContainsConst($funcCall->args[1]->value, "ENT_QUOTES")) {
                        return [];
                    }
                    return $funcCall->args[0]->taint;
                };
                $handler = new ReturnCallableHandler($can, $do);
                $function->addReturnedTaintHandler($handler);
            }

            { // if we want sanitizer to be NONE, htmlspecialchars never returns taint
                $can = $this->handlers_can_sanitation("NONE");
                $do = $this->handlers_return_noTaint();
                $handler = new ReturnCallableHandler($can, $do);
                $function->addReturnedTaintHandler($handler);
            }

        }

        // input tainted chain
        {
            $can = $this->handlers_can_always();
            $do = $this->handlers_input_argument(0);
            $handler = new ReturnCallableHandler($can, $do);
            $function->addTaintedInputHandler($handler);
        }

        // replace chain
        {
            { // sanitation === NONE
                $can = $this->handlers_can_sanitation("NONE");
                $do = $this->handlers_replace_returnArgument(0);
                $handler = new ReturnCallableHandler($can, $do);
                $function->addReplaceHandler($handler);
            }
            { // sanitation === INSUFFICIENT_ENCODING
                // make sure htmlspecialchars is called without ENT_QUOTES
                $can = $this->handlers_can_sanitation("INSUFFICIENT_ENCODING");
                $do = function (FuncCall $funcCall, $options) {
                    return $this->handlers_replace_htmlspecialchars($funcCall->args[0]);
                };
                $handler = new ReturnCallableHandler($can, $do);
                $function->addReplaceHandler($handler);
            }
        }

        return $function;
    }

    // TODO better!
    private function _function_str_replace () {
        $function = new Sanitizer("str_replace");

        // returned taint chain
        {
            $can = $this->handlers_can_always();
            $do = $this->handlers_return_taintFromArgument(2);
            $handler = new ReturnCallableHandler($can, $do);
            $function->addReturnedTaintHandler($handler);
        }

        // input tainted chain
        {
            $can = $this->handlers_can_always();
            $do = $this->handlers_input_argument(2);
            $handler = new ReturnCallableHandler($can, $do);
            $function->addTaintedInputHandler($handler);
        }

        // replace chain
        {
            // sanitation === NONE
            {
                $can = $this->handlers_can_sanitation("NONE");
                $do = $this->handlers_replace_returnArgument(2);
                $handler = new ReturnCallableHandler($can, $do);
                $function->addReplaceHandler($handler);
            }
            { // sanitation === INSUFFICIENT_ENCODING
                // make sure htmlspecialchars is called without ENT_QUOTES
                $can = $this->handlers_can_sanitation("INSUFFICIENT_ENCODING");
                $do = function (FuncCall $funcCall, $options) {
                    return $this->handlers_replace_htmlspecialchars($funcCall->args[2]);
                };
                $handler = new ReturnCallableHandler($can, $do);
                $function->addReplaceHandler($handler);
            }
        }

        return $function;
    }

    /* Commonly used functions in handlers */
    // can
    public function handlers_can_always () {
        return function (FuncCall $funcCall, $options) {
            return true;
        };
    }

    //// returns true if $options->sanitation === $sanitation
    public function handlers_can_sanitation ($sanitation) {
        return function (FuncCall $funcCall, $options) use ($sanitation) {
            if ($options->sanitation === $sanitation) {
                return true;
            }
            return false;
        };
    }

    // return
    //// returns taint from argument number $num
    public function handlers_return_taintFromArgument($num) {
        return function (FuncCall $funcCall, $options) use ($num) {
            print_r($funcCall->args[$num]->taint);
            return $funcCall->args[$num]->taint;
        };
    }

    //// returns the taint $taint
    public function handlers_return_taint($taint) {
        return function (FuncCall $funcCall, $options) use ($taint) {
            return $taint;
        };
    }

    //// returns no taint
    public function handlers_return_noTaint () {
        return $this->handlers_return_taint([]);
    }

    // replace
    //// returns the argument number $num
    public function handlers_replace_returnArgument ($num) {
        return function (FuncCall $funcCall, $options) use ($num) {
            return $funcCall->args[$num]->value;
        };
    }

    //// ändra
    public function handlers_replace_htmlspecialchars ($argument) {
        $name = new Name("htmlspecialchars");
        $funcCall = new FuncCall($name, [$argument]);
        return $funcCall;
    }

    // input tainted
    //// retuns true if argument number $num has no taint
    public function handlers_input_argument ($num) {
        return function (FuncCall $funcCall, $options) use ($num) {
            return !empty($funcCall->args[$num]->taint);
        };
    }

    public function __construct() {
        $this->_functions = [];
    }

    private $_functions;
}

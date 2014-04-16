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
        if (!isset($this->_functions[$function])) {
            $file = $this->_functionFile($function);
            if (file_exists($file)) {
                $sanFunction = require $file;
                $this->add(call_user_func($sanFunction));
            } else {
                throw new \Exception(sprintf("file [%s] doesn't exist", $file));
            }
        }

        return $this->_functions[$function];
    }

    public function exists ($function) {
        if (isset($this->_functions[$function])) {
            return true;
        }
        $file = $this->_functionFile($function);
        if (file_exists($file)) {
            return true;
        }

        return false;
    }

    private function _functionFile ($function) {
        //echo __DIR__ . "/SanitationFunctions/" . $function . ".php\n";
        return __DIR__ . "/SanitationFunctions/" . $function . ".php";
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

    public function __construct() {
        $this->_functions = [];
    }

    private $_functions;
}

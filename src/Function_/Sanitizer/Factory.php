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
        return __DIR__ . "/SanitationFunctions/" . $function . ".php";
    }

    public function __construct() {
        $this->_functions = [];
    }

    private $_functions;
}

<?php
namespace Phulner\Function_\Sanitizer;

use PhpParser\Node\Expr\FuncCall;

class ReturnCallableHandler {
    public function __construct(callable $canHandleFunction, callable $doFunction, ReturnCallableHandler $successor = NULL) {
        $this->_doFunction = $doFunction;
        $this->_canHandle = $canHandleFunction;
        $this->_successor = $successor;
    }

    public function setSuccessor (ReturnCallableHandler $successor) {
        $this->_successor = $successor;
    }

    public function handleRequest (FuncCall $funcCall, $options) {
        if ($this->canHandle($funcCall, $options)) {
            return $this->doit($funcCall, $options);
        }
        if ($this->_successor) {
            return $this->_successor->handleRequest($funcCall, $options);
        }
        return null;
    }

    protected function canHandle (FuncCall $funcCall, $options) {
        return call_user_func($this->_canHandle, $funcCall, $options);
    }

    protected function doit (FuncCall $funcCall, $options) {
        return call_user_func($this->_doFunction, $funcCall, $options);
    }

    private $_doFunction;
    private $_canHandle;
    private $_successor;

}

?>

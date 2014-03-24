<?php
namespace Phulner\Function_;

use PhpParser\Node\Expr\FuncCall;

use Phulner\Function_\Sanitizer\ReturnCallableHandler;

class Sanitizer {
    public function __construct ($name) {
        $this->_name = $name;
    }

    public function getName () {
        return $this->_name;
    }

    public function addReplaceHandler (ReturnCallableHandler $handler) {
        if ($this->_replaceChain) {
            $handler->setSuccessor($this->_replaceChain);
        }
        $this->_replaceChain = $handler;
    }

    public function addTaintedInputHandler (ReturnCallableHandler $handler) {
        if ($this->_taintedInputChain) {
            $handler->setSuccessor($this->_taintedInputChain);
        }
        $this->_taintedInputChain = $handler;
    }

    public function addReturnedTaintHandler (ReturnCallableHandler $handler) {
        if ($this->_returnedTaintChain) {
            $handler->setSuccessor($this->_returnedTaintChain);
        }
        $this->_returnedTaintChain = $handler;
    }

    public function replace (FuncCall $funcCall, $options) {
        return $this->_replaceChain->handleRequest($funcCall, $options);
    }

    public function returnedTaint (FuncCall $funcCall, $options) {
        return $this->_returnedTaintChain->handleRequest($funcCall, $options);
    }

    public function taintedInput (FuncCall $funcCall, $options) {
        return $this->_taintedInputChain->handleRequest($funcCall, $options);
    }

    private $_name;

    private $_taintedInputChain;
    private $_returnedTaintChain;
    private $_referencedInputChain;
    private $_replaceChain;
}


?>

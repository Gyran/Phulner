<?php
namespace Phulner\NodeVisitor;

use Phulner\Function_\Sanitizer\Factory;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Expr\FuncCall;

class Replacer extends NodeVisitorAbstract {
    public function __construct(Factory $factory, $options) {
        $this->setSanitationFunctionFactory($factory);
        $this->setOptions($options);
    }

    public function setOptions ($options) {
        $this->_options = $options;
    }

    public function setSanitationFunctionFactory (Factory $factory) {
        $this->_sanitationFunctionFactory = $factory;
    }

    public function leaveNode (Node $node) {
        // only replace function calls
        if (!($node instanceof FuncCall)) {
            return;
        }
        if ($this->_removesTaint($node)) {
            $functionName = $node->name->toString();
            $sanitizer = $this->_sanitationFunctionFactory->get($functionName);

            $newNode = $sanitizer->replace($node, $this->_options);
            echo "replejsar" , $node->name ,"\n";
            return $newNode;
        }

    }

    private function _removesTaint(FuncCall $funcCall) {
        $functionName = $funcCall->name->toString();
        if ($this->_sanitationFunctionFactory->exists($functionName)) {
            $sanitizer = $this->_sanitationFunctionFactory->get($functionName);

            if ($sanitizer->taintedInput($funcCall, $this->_options)) {
                if (empty($funcCall->taint)) {
                    return true;
                }
            }
        }
        return false;
    }

    private $_sanitationFunctionFactory;
    private $_options;
}

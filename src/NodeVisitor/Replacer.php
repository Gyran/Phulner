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
        $method = "_leaveNode_" . $node->getType();
        if (method_exists($this, $method)) {
            return $this->$method($node);
        }
        //echo stringColor("Replacer: Missing method " . $method . "\n", "1;31");
    }

    private function _leaveNode_Expr_FuncCall (Node\Expr\FuncCall $node) {
        if ($this->_removesTaint($node)) {
            $functionName = $node->name->toString();
            $sanitizer = $this->_sanitationFunctionFactory->get($functionName);

            $newNode = $sanitizer->replace($node, $this->_options);
            echo stringColor(sprintf("Replacing function %s\n", $functionName), "1;32");
            return $newNode;
        }
    }

    private function _leaveNode_Expr_BinaryOp_Plus (Node\Expr\BinaryOp\Plus $node) {
        return $this->_leaveNode_Expr_BinaryOp_Number($node);
    }

    private function _leaveNode_Expr_BinaryOp_Minus (Node\Expr\BinaryOp\Minus $node) {
        return $this->_leaveNode_Expr_BinaryOp_Number($node);
    }

    private function _leaveNode_Expr_BinaryOp_Number (Node\Expr\BinaryOp $node) {
        if ($node->left instanceof Node\Scalar) {
            // left node is scalar
            if (!empty($node->right->taint)) {
                return $node->right;
            }
        } else if ($node->right instanceof Node\Scalar) {
            // right node is scalar
            if (!empty($node->left->taint)) {
                return $node->left;
            }
        } else { // none is scalar

        }
    }

    private function _removesTaint (Node $node) {
        $method = "_removesTaint_" . $node->getType();
        if (method_exists($this, $method)) {
            return $this->$method($node);
        }
    }

    private function _removesTaint_Expr_FuncCall (Node\Expr\FuncCall $node) {
        $functionName = $node->name->toString();
        if ($this->_sanitationFunctionFactory->exists($functionName)) {
            $sanitizer = $this->_sanitationFunctionFactory->get($functionName);

            if ($sanitizer->taintedInput($node, $this->_options)) {
                if (empty($node->taint)) {
                    return true;
                }
            }
        }
        return false;
    }

    private $_sanitationFunctionFactory;
    private $_options;
}

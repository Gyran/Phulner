<?php
namespace Phulner\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

use Phulner\Function_\Sanitizer\Factory;
use Phulner\NodeVisitor\Scope\Variable;

class Tainter extends NodeVisitorAbstract {

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

    public function beforeTraverse (array $nodes) {

    }

    public function afterTraverse (array $nodes) {

    }

    public function leaveNode (Node $node) {
        $method = "_leaveNode_" . $node->getType();
        if (method_exists($this, $method)) {
            return $this->$method($node);
        }
        //echo stringColor("Tainter: Missing method " . $method . "\n", "1;31");
    }

    private function _leaveNode_Arg (Node\Arg $node) {
        $node->taint = $node->value->taint;
    }

    private function _leaveNode_Expr_FuncCall (Node\Expr\FuncCall $node) {
        $node->taint = $this->_returnsTaint($node);
    }

    private function _leaveNode_Expr_ArrayDimFetch (Node\Expr\ArrayDimFetch $node) {
        $node->taint = $node->scopeVar->getTaint();
    }

    private function _leaveNode_Expr_Variable (Node\Expr\Variable $node) {
        $node->taint = $node->scopeVar->getTaint();
    }

    private function _leaveNode_Expr_Assign (Node\Expr\Assign $node) {
        $taint = $node->expr->taint;
        $node->var->scopeVar->setTaint($taint);
        $node->var->taint = $taint;
        $node->taint = $taint;
    }

    private function _leaveNode_Expr_BinaryOp_Plus (Node\Expr\BinaryOp\Plus $node) {
        $node->taint = [];
    }

    private function _leaveNode_Expr_BinaryOp_Minus (Node\Expr\BinaryOp\Minus $node) {
        $node->taint = [];
    }

    private function _returnsTaint (Node\Expr $expr) {
        $method = "_returnsTaint_" . $expr->getType();
        if (method_exists($this, $method)) {
            return $this->$method($expr);
        }
        echo stringColor("Tainter: Missing method " . $method . "\n", "1;31");
        return [];
    }

    private function _returnsTaint_Expr_FuncCall (Node\Expr\FuncCall $node) {
        $functionName = $node->name->toString();

        $sanitizer = $this->_getSanitizer($functionName);
        if ($sanitizer) {
            if ($sanitizer->taintedInput($node, $this->_options)) {
                return $sanitizer->returnedTaint($node, $this->_options);
            }
        }
    }

    private function _getSanitizer ($name) {
        if ($this->_sanitationFunctionFactory->exists($name)) {
            return $this->_sanitationFunctionFactory->get($name);
        }
        return null;
    }

    private function _removesTaint (Expr $expr) {
        $expr->removesTaint = false;
    }

    private $_sanitationFunctionFactory;
    private $_options;

    private $_currentScope;
    private $_scopes;
}


?>

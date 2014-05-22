<?php
namespace Phulner\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\NodeVisitorAbstract;

use Phulner\NodeVisitor\Scope\Variable;
use Phulner\NodeVisitor\Scope\VariableAbstract;

class Scoper extends NodeVisitorAbstract {
    public function __construct(Scope $initialScope, $options) {
        $this->setInitialScope($initialScope);
        $this->setOptions($options);
    }

    public function setInitialScope (Scope $initialScope) {
        $this->_initialScope = $initialScope;
    }

    public function setOptions ($options) {
        $this->_options = $options;
    }

    public function beforeTraverse (array $nodes) {
        unset($this->_scopes);
        $this->_scopes = new \SplStack();

        $this->_currentScope = $this->_initialScope;
    }

    public function afterTraverse (array $nodes) {

    }

    public function enterNode (Node $node) {
        $method = "_enterNode_" . $node->getType();
        if (method_exists($this, $method)) {
            return $this->$method($node);
        }
    }

    private function _enterNode_Expr_Variable (Node $node) {
        if (isset($node->scopeVar)) {
            // already visited
            return;
        }

        if (!$this->_currentScope->hasVariable($node->name)) {
            // variable does not exist in scope, add it to the scope
            $var = new Variable\Variable($node->name);
            $this->_currentScope->addVariable($var);
        }
        $scopeVar = &$this->_currentScope->getVariable($node->name);
        $node->scopeVar = $scopeVar;
    }

    private function _enterNode_Expr_ArrayDimFetch (Node\Expr\ArrayDimFetch $node) {
        return;
        if (isset($node->scopeVar)) {
            // already visited
            return;
        }

        // get the variable name and the path
        $innerVarNode = $this->_ArrayDimFetch_innerVar($node);
        $pathToInnerVarNode = $this->_ArrayDimFetch_innerVarPath($node);

        // check if the inner var is in scope
        if (!$this->_currentScope->hasVariable($innerVarNode->name)) {
            // if not then create it
            $this->_currentScope->addVariable(new Variable\Array_($innerVarNode->name));
        }
        $var = $this->_currentScope->getVariable($innerVarNode->name);

        // walk the path to make sure all levels exists
        foreach ($pathToInnerVarNode as &$pathNode) {
            $var = &$this->_varGetByNode($var, $pathNode);
            $pathNode->scopeVar = $var;
        }
    }

    public function leaveNode (Node $node) {
        $method = "_leaveNode_" . $node->getType();
        if (method_exists($this, $method)) {
            return $this->$method($node);
        }
    }

    private function _leaveNode_Expr_ArrayDimFetch (Node\Expr\ArrayDimFetch $node) {
        $var = &$this->_varGetByNode($node);
        $node->scopeVar = $var;
    }

    private function _leaveNode_Expr_Assign (Node $node) {
        $retVar = $this->_returnsVariable($node->var->scopeVar->getName(), $node->expr);
        $node->var->scopeVar = $retVar;

        if ($node->var instanceof Node\Expr\Variable) {
            $this->_currentScope->addVariable($retVar);
        } elseif ($node->var instanceof Node\Expr\ArrayDimFetch) {
            $varNode = $this->_ArrayDimFetch_innerVarNode($node->var);
            $var = $this->_currentScope->getVariable($varNode->name);
            $arr = $this->_ArrayDimFetch_getArray($var, $node->var);
            $arr->addKey($retVar);
        }

    }

    private function _returnsVariable ($name, Node $node) {
        $method = "_returnsVariable_" . $node->getType();
        if (method_exists($this, $method)) {
            return $this->$method($name, $node);
        }

        return new Variable\Variable($name);
        throw new \Exception(sprintf("returns variable from %s not supported", $node->getType()));

    }

    private function _returnsVariable_Scalar_String ($name, Node $node) {
        $var = new Variable\Variable($name);
        $var->setValue($this->_returnsValue($node));
        return $var;
    }

    private function _returnsVariable_Expr_Variable ($name, Node $node) {
        $var = clone $node->scopeVar;
        $var->setName($name);
        return $var;
    }

    /*private function _returnsVariable_Expr_ArrayDimFetch ($name, Node $node) {
        print_r($node);
        $var = clone $node->scopeVar;
        $var->setName($name);
        return $var;
    }*/

    private function _returnsValue ($node) {
        $method = "_returnsValue_" . $node->getType();
        if (method_exists($this, $method)) {
            return $this->$method($node);
        }
    }

    private function _returnsValue_Scalar_String (Node\Scalar\String $node) {
        return $node->value;
    }

    // From a ArrayDimFetch, find the inner Node\Expr\Variable
    private function _ArrayDimFetch_innerVarNode (Node\Expr\ArrayDimFetch $node) {
        $var = $node->var;
        while (!($var instanceof Node\Expr\Variable)) {
            $var = $var->var;
        }

        return $var;
    }

    // Given an array and an ArrayDimFetch, follows that path and returns the innermost array
    private function _ArrayDimFetch_getArray (Variable\Array_ $arr, Node\Expr\ArrayDimFetch $node) {
        $var = $node->var;
        while  (!$var instanceof Node\Expr\Variable) {
            $key = $this->_nodeToValue($node->dim);
            $arr = $arr->getKey($key);
        }

        return $arr;
    }

    private function _ArrayDimFetch_innerVarPath (Node\Expr\ArrayDimFetch $node) {
        $var = $node->var;
        $path = [];
        $path[] = $node;
        while (!($var instanceof Node\Expr\Variable)) {
            $path[] = $var;
            $var = $var->var;
        }

        return array_reverse($path);
    }

    private function _nodeToValue (Node $node) {
        if ($node instanceof Node\Scalar) {
            return $node->value;
        }
        if ($node instanceof Node\Expr\Variable) {
            if ($node->scopeVar->getValueSet()) {
                return $node->scopeVar->getValue();
            }
        }
        if ($node instanceof Node\Expr\ArrayDimFetch) {
            if ($node->scopeVar->getValueSet()) {
                return $node->scopeVar->getValue();
            }
        }

        return false;
    }

    private function _nodepathInVariable (VariableAbstract $var, Node $node) {
        $method = "_nodepathInVariable_" . $node->getType();
        if (method_exists($this, $method)) {
            return $this->$method($var, $node);
        }

        return false;
    }

    private function &_varGetByNode (Node $node) {
        $method = "_varGetByNode_" . $node->getType();
        if (method_exists($this, $method)) {
            $var = &$this->$method($node);
            return $var;
        }

        throw new \Exception(sprintf("Missing method [%s]", $method));
    }


    private function &_varGetByNode_Expr_ArrayDimFetch (Node\Expr\ArrayDimFetch $node) {
        $key = $this->_nodeToValue($node->dim);
        $var = $node->var->scopeVar;

        if ($key === false) {
            throw new \Exception("could not get key");
        }

        // check if key exists, if not: create it
        if (!$var->hasKey($key)) {
            $taint = [];

            if ($var->getInherit()) {
                $taint = $var->getTaint();
            }

            if ($node->var instanceof Node\Expr\ArrayDimFetch) {
                $var->addKey(new Variable\Array_($key, $taint, $var->getInherit()));
            } else {
                $var->addKey(new Variable\Variable($key, $taint));
            }
        }

        $var = &$var->getKey($key);

        return $var;
    }

    private function &_varGetByNode_Expr_Variable (VariableAbstract $var, Node\Expr\Variable $node) {
        if ($var instanceof Variable\Array_) {
            $var = &$var->getKey($node->name);
            return $var;
        }
        return null;
    }

    private function _nodepathInVariableArrayDimFetch (VariableAbstract $var, Node\Expr\ArrayDimFetch $node) {
        $key = $this->_nodeToValue($node->dim);

        if ($key === false) {
            throw new \Exception("Could not evaluate dim");
        }

        // does the key exists? if not, create it!
        if ($var->hasKey($key)) {
            //if ()
        }

    }

    private $_initialScope;
    private $_options;
}

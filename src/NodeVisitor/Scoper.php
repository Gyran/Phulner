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
        //print_r($this->_currentScope);
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
        $node->scopeVar = $this->_currentScope->getVariable($node->name);
        print_r($node);
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
            $var = $this->_varGetByNode($var, $pathNode);
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
        $var = $this->_varByNode($node);
    }

    private function _leaveNode_Expr_Assign (Node $node) {

        if ($node->var instanceof Node\Expr\Variable) {
            $var = $this->_returnsVariable($node->var->scopeVar->getName(), $node->expr);
            $this->_currentScope->addVariable($var);
            $var = $this->_currentScope->getVariable($var->getName());
            $node->var->scopeVar = $var;
        }

        echo "================\n";
        //print_r($node->var->scopeVar);
        echo $this->_nodeToValue($node->var);
        echo "================\n";

    }

    private function _returnsVariable ($name, Node $node) {
        $method = "_returnsVariable_" . $node->getType();
        if (method_exists($this, $method)) {
            return $this->$method($name, $node);
        }
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

    private function _returnsVariable_Expr_ArrayDimFetch ($name, Node $node) {
        $var = clone $node->scopeVar;
        $var->setName($name);
        return $var;
    }

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
    private function _ArrayDimFetch_innerVar (Node\Expr\ArrayDimFetch $node) {
        $var = $node->var;
        while (!($var instanceof Node\Expr\Variable)) {
            $var = $var->var;
        }

        return $var;
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

        return false;
    }

    private function _nodepathInVariable (VariableAbstract $var, Node $node) {
        $method = "_nodepathInVariable_" . $node->getType();
        if (method_exists($this, $method)) {
            return $this->$method($var, $node);
        }

        return false;
        //throw new \Exception(sprintf("Can't convert %s to a Variable", $node->getType()));
    }

    private function _varGetByNode (VariableAbstract $var, Node $node) {
        $method = "_varGetByNode_" . $node->getType();
        if (method_exists($this, $method)) {
            return $this->$method($var, $node);
        }

        throw new \Exception(sprintf("Missing method [%s]", $method));
    }


    private function _varGetByNode_Expr_ArrayDimFetch (VariableAbstract $var, Node\Expr\ArrayDimFetch $node) {
        $key = $this->_nodeToValue($node->dim);

        echo "getting key [", $key, "], var is [", $var->getType(), "] and [", $var->getName(), "\n";

        if (!($var instanceof Variable\Array_)) {
            return $var;
        }

        if ($key === false) {
            throw new \Exception("could not get key");
        }

        // check if key exists, if not: create it
        if (!$var->hasKey($key)) {
            if ($var->getInherit()) {
                $var->addKey(new Variable\Array_($key, $var->getTaint(), true));
            } else {
                $var->addKey(new Variable\Array_($key));
            }
        }
        $var = $var->getKey($key);

        return $var;
        //return $this->_varGetByNode($var, $node->var);
    }

    private function _varGetByNode_Expr_Variable (VariableAbstract $var, Node\Expr\Variable $node) {
        echo "getting var [", $node->name, "], var is [", $var->getType(), "] and [", $var->getName(), "\n";
        if ($var instanceof Variable\Array_) {
            return $var->getKey($node->name);
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

            /*
            $key = $this->_nodeToValue($pathNode->dim);

            if ($key === false) {
                throw new \Exception("Could not evaluate dim");
            }

            // does the key exist? if not, create it
            if (!$scopeVar->hasKey($key)) {
                if ($scopeVar->getInherit()) {
                    $scopeVar->addKey(new Variable\Array_($key, $scopeVar->getTaint(), true));
                } else {
                    $scopeVar->addKey(new Variable\Array_($key));
                }
            }

            $scopeVar = $scopeVar->getKey($key);
            */


    private $_initialScope;
    private $_options;
}

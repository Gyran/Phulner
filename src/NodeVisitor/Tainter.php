<?php
namespace Phulner\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;

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
        $this->white = "";
        unset($this->_scopes);
        $this->_scopes = new \SplStack();

        $this->_currentScope = $this->_initialScope;
    }

    public function afterTraverse (array $nodes) {
        print_r($this->_currentScope);
    }

    public $white;

    public function enterNode (Node $node) {
        //echo $this->white . "entering node ", get_class($node), "\n";

        //$this->white .= "    ";

        $method = "_enterNode_" . $node->getType();
        if (method_exists($this, $method)) {
            return $this->$method($node);
        }

        // add taint properties to all nodes
        $node->taint = [];
    }

    public function leaveNode (Node $node) {
        //$this->white = substr($this->white, 4);
        //echo $this->white . "leaving node ", get_class($node), "\n";
        //$taint = [];

        $method = "_leaveNode_" . $node->getType();
        if (method_exists($this, $method)) {
            return $this->$method($node);
        }

        /*if ($node instanceof Expr\Variable) {
            $var = &$this->_currentScope->getVariable($node->name);
            $node->scopeVar = $var;

            if ($var) {
                $taint = $var->getTaint();
            }
        } elseif ($node instanceof Expr\ArrayDimFetch) {
            if ($node->dim instanceof Scalar\String) {
                $var = $node->var->scopeVar->getKey($node->dim->value);
            }

            $node->scopeVar = $var;
            if ($var) {
                $taint = $var->getTaint();
            }
        } elseif ($node instanceof Expr\Assign) {
            // Get the taint from the expression
            $taint = $node->expr->taint;

            if ($node->var instanceof Expr\Variable) {
                // If it is assigned to a Variable
                $var = new Variable($node->var->name, $taint);

                // Add the taint to the variable node
                $node->var->taint = $taint;
                // And save it to the current scope
                $this->_currentScope->addVariable($var);
                $node->var->scopeVar = $this->_currentScope->getVariable($var->getName());
            } elseif ($node->var instanceof Expr\ArrayDimFetch) {
                $var = new Variable($node->var->dim)
                $node->var->scopeVar = $var;
            }
        } elseif ($node instanceof Node\Arg) {
            $taint = $this->_returnsTaint($node->value);
        } elseif ($node instanceof Expr\FuncCall) {
            $taint = $this->_returnsTaint($node);
        }

        $node->taint = $taint;*/
    }

    private function _enterNode_Expr_ArrayDimFetch (Node $node) {
        if (!isset($node->scopeVar)) {
            // this is the root dim fetch
        }
    }

    private function _leaveNode_Expr_Variable (Node $node) {
        $node->taint = $node->scopeVar->getTaint();
    }

    private function _leaveNode_Expr_Assign (Node $node) {
        $taint = $node->expr->taint;
        $node->var->scopeVar->setTaint($taint);
        $node->var->taint = $taint;
        $node->taint = $taint;
    }

    private function _returnsTaint (Expr $expr) {
        $taint = [];
        // Its a variable, return the variables taint
        if ($expr instanceof Expr\Variable ||
            $expr instanceof Expr\ArrayDimFetch) {
            $taint = $expr->taint;
        } elseif ($expr instanceof Expr\Assign) {
            $taint = $this->_returnsTaint($expr->var);
        } elseif ($expr instanceof Expr\FuncCall) {
            $functionName = $expr->name->toString();
            if ($this->_sanitationFunctionFactory->exists($functionName)) {
                $sanitizer = $this->_sanitationFunctionFactory->get($functionName);

                if ($sanitizer->taintedInput($expr, $this->_options)) {
                    // input is tainted

                    $taint = $sanitizer->returnedTaint($expr, $this->_options);


                }


            }

        }


        //echo get_class($expr), "\n";
        return $taint;

    }

    private function _removesTaint (Expr $expr) {
        $expr->removesTaint = false;
    }

    //private $_initialScope;
    private $_sanitationFunctionFactory;
    private $_options;

    private $_currentScope;
    private $_scopes;
}


?>

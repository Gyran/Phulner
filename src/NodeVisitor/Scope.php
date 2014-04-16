<?php
namespace Phulner\NodeVisitor;

use Phulner\NodeVisitor\Scope\Variable;
use Phulner\NodeVisitor\Scope\VariableAbstract;

use PhpParser\Node\Expr;

class Scope {
    public function addVariable (VariableAbstract &$var) {
        $this->_variables[$var->getName()] = &$var;
    }

    public function hasVariable ($name) {
        return isset($this->_variables[$name]);
    }

    public function &getVariable ($name) {
        $var = &$this->_variables[$name];
        return $var;
    }

    public function addFromConfig ($config) {
        $var = $this->_variableFromConfig($config);


        if ($var) {
            $this->_variables[$var->getName()] = $var;
        }
    }

    private function _variableFromConfig ($config) {
        $method = "_variableFromConfig_" . $config->type;
        if (method_exists($this, $method)) {
            return $this->$method($config);
        }

        return null;
    }

    private function _variableFromConfig_variable ($config) {
        return new Variable\Variable($config->name, $config->taint);
    }

    private function _variableFromConfig_array ($config) {
        $keys = [];
        if (isset($config->keys)) {
            foreach ($config->keys as $key) {
                $var = $this->_variableFromConfig($key);
                if ($var) {
                    $keys[$var->getName()] = $var;
                }
            }
        }

        $inherit = false;
        if (isset($config->inherit)) {
            $inherit = $config->inherit;
        }

        return new Variable\Array_($config->name, $config->taint, $inherit, $keys);
    }

    private $_variables;
}

?>

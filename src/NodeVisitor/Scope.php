<?php
namespace Phulner\NodeVisitor;

use Phulner\NodeVisitor\Scope\Variable;

use PhpParser\Node\Expr;

class Scope {
    public function add (Variable $var) {
        $this->_variables[$var->getName()] = $var;
    }

    public function getVariable ($var) {
        if (isset($this->_variables[$var])) {
            return $this->_variables[$var];
        }
        return null;
    }

    public function hasVar ($var) {
        return isset($this->_variables[$var]);
    }

    public function addFromConfig ($config) {
        $var = $this->_taintableFromConfig($config);

        if ($var) {
            $this->_variables[$var->getName()] = $var;
        }
    }

    private function _taintableFromConfig ($config) {
        $method = "_taintableFromConfig_" . $config->type;
        if (method_exists($this, $method)) {
            return $this->$method($config);
        }

        return null;
    }

    private function _taintableFromConfig_variable ($config) {
        return new Variable($config->name, $config->taint);
    }

    private function _taintableFromConfig_array ($config) {
        $keys = [];
        if (isset($config->keys)) {
            foreach ($config->keys as $key) {
                $var = $this->_taintableFromConfig($key);
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

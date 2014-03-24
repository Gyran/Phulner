<?php
namespace Phulner\NodeVisitor;

use Phulner\NodeVisitor\Scope\Taintable;

use PhpParser\Node\Expr;

class Scope {
    public function add (Taintable $var) {
        $this->_variables[$var->getName()] = $var;
    }

    public function getFromVariable (Expr\Variable $expr) {
        if (isset($this->_variables[$expr->name])) {
            return $this->_variables[$expr->name];
        }

        return null;
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
        return new Taintable\Variable($config->name, $config->taint);
    }

    private function _taintableFromConfig_array ($config) {
        $keys = [];
        foreach ($config->keys as $key) {
            $var = $this->_taintableFromConfig($key);
            if ($var) {
                $keys[$var->getName()] = $var;
            }
        }

        return new Taintable\Array_($config->name, $config->taint, $config->inherit, $keys);
    }

    private $_variables;
}

?>

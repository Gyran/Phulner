<?php
namespace Phulner\Injector;

use Phulner\InjectorAbstract;

class Csrf extends InjectorAbstract {
    public function __construct($action) {
        $method = "_action_" . $action;

        if (method_exists($this, $method)) {
            $this->_method = $method;
        } else {
            // should not happen
        }
    }

    public function inject ($code, $options) {
        $ret =        "// Phulner Injection start\n";
        $ret = $ret . "/* Old code:\n";
        $ret = $ret . $code . "*/\n";
        $ret = $ret . $this->{$this->_method}($code, $options);
        $ret = $ret . "// Phulner Injection end\n";
        return  $ret;
    }

    private function _action_guard ($code, $options) {
        if ($options->type === "NONE") {
            return "";
        }
        if ($options->type === "ONLY_POST") {
            $ret =
<<<'CODE'
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die();
}
CODE;
            return $ret;
        }
        if ($options->type === "COMPUTABLE") {
            $ret =
<<<'CODE'
if (!isset($_POST["phulner_csrf_token"]) || $_POST["phulner_csrf_token"] !== md5(date("Y-m-d"))) {
    die();
}
CODE;
            return $ret;
        }
    }

    private function _action_generate ($code, $options) {
        if ($options->type === "COMPUTABLE") {
            $ret =
<<<'CODE'
$phulner_csrf_token = md5(date("Y-m-d"));
CODE;
            return $ret;
        }
        return "";
    }

    private function _action_include ($code, $options) {
        if ($options->type === "COMPUTABLE") {
            $ret =
<<<'CODE'
echo "<input type='hidden' name='phulner_csrf_token' value='", $phulner_csrf_token, "'>";
CODE;
            return $ret;
        }
        return "";
    }

    private $_method;
}

?>

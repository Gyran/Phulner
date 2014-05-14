<?php
namespace Phulner\Injector;

use PhpParser\Parser;
use PhpParser\NodeTraverser;

use Phulner\InjectorAbstract;
use Phulner\NodeVisitor\Tainter;
use Phulner\NodeVisitor\Replacer;
use Phulner\NodeVisitor\Scoper;
use Phulner\NodeVisitor\Scope;
use Phulner\Function_\Sanitizer\Factory;
use Phulner\PhpParser\Lexer;
use Phulner\PhpParser\PrettyPrinter;
use Phulner\PhpParser\NodeDumper;

class Xss extends InjectorAbstract {
    const DEFAULT_METHOD = "tainting";

    public function __construct ($partConfig, $wholeConfig) {
        $this->_method = self::DEFAULT_METHOD;
        $this->_partConfig = $partConfig;
        $this->_wholeConfig = $wholeConfig;
    }

    public function setSanitationFunctionsFactory (Factory $factory) {
        $this->_sanitationFunctionsFactory = $factory;
    }

    public function setInitialScope (Scope $scope) {
        $this->_initialScope = $scope;
    }

    public function setMethod ($method) {
        $this->_method = $method;
    }

    public function inject($code, $options) {
        $injectMethod = $this->_method;
        if (isset($options->method)) {
            $injectMethod = $options->method;
        }

        $method = "inject_" . $injectMethod;

        if (method_exists($this, $method)) {
            return $this->$method($code, $options);
        }

        throw new \Exception(sprintf("Method %s not defined", $method));
    }

    public function inject_tainting ($code, $options) {
        if (!in_array($options->sanitation, $this->_partConfig->sanitation)) {
            $options->sanitation = $this->_partConfig->sanitation[0];
        }

        if (!isset($options->output) || !in_array($options->output, $this->_wholeConfig->output)) {
            $options->output = $this->_wholeConfig->output[0];
        }

        $nodeDumper = new NodeDumper;
        $prettyPrinter = new PrettyPrinter;

        $parser = new Parser(new Lexer);

        $statements = $parser->parse("<?php\n" . $code);

        $traverser = new NodeTraverser;

        $scoper = new Scoper($this->_initialScope, $options);
        $traverser->addVisitor($scoper);
        $tainter = new Tainter($this->_sanitationFunctionsFactory, $options);
        $traverser->addVisitor($tainter);
        $replacer = new Replacer($this->_sanitationFunctionsFactory, $options);
        $traverser->addVisitor($replacer);

        $statements = $traverser->traverse($statements);

        $ret =        "// Phulner Injection start (tainting)\n";
        $ret = $ret . "/* Old code:\n";
        $ret = $ret . $code . "*/\n";
        $ret = $ret . $prettyPrinter->prettyPrint($statements) . "\n";
        $ret = $ret . "// Phulner Injection end\n";
        return  $ret;
    }

    public function inject_removeing ($code, $options) {
        $ret =      "// Phulner Injection start (removeing)\n";
        $ret = $ret . "/* Old code:\n";
        $ret = $ret . $code . "*/\n";
        $ret = $ret . "// Phulner Injection end\n";
        return $ret;
    }


    private $_sanitationFunctionsFactory;
    private $_initialScope;
    private $_method;
    private $_partConfig;
    private $_wholeConfig;
}

?>

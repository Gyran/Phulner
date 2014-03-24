<?php
namespace Phulner\Injector;

use PhpParser\Parser;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;

use Phulner\InjectorAbstract;
use Phulner\NodeVisitor\Tainter;
use Phulner\NodeVisitor\Replacer;
use Phulner\NodeVisitor\Scope;
use Phulner\Function_\Sanitizer\Factory;

class Xss extends InjectorAbstract {
    public function __construct () {

    }

    public function setSanitationFunctionsFactory (Factory $factory) {
        $this->_sanitationFunctionsFactory = $factory;
    }

    public function setInitialScope (Scope $scope) {
        $this->_initialScope = $scope;
    }

    public function inject($code, $options) {
        $nodeDumper = new \PhpParser\NodeDumper;
        $prettyPrinter = new \PhpParser\PrettyPrinter\Standard;

        $parser = new Parser(new Lexer);

        $statements = $parser->parse("<?php\n" . $code);

        $traverser = new NodeTraverser;

        $tainter = new Tainter($this->_initialScope, $this->_sanitationFunctionsFactory, $options);
        $traverser->addVisitor($tainter);
        $replacer = new Replacer($this->_sanitationFunctionsFactory, $options);
        $traverser->addVisitor($replacer);

        $statements = $traverser->traverse($statements);

        var_dump($statements);

        //echo $nodeDumper->dump($statements), "\n";
        //echo $prettyPrinter->prettyPrint($statements), "\n";

        $ret =        "// Phulner Injection start\n";
        $ret = $ret . "/* Old code:\n";
        $ret = $ret . $code . "*/\n";
        $ret = $ret . $prettyPrinter->prettyPrint($statements) . "\n";
        $ret = $ret . "// Phulner Injection end\n";
        return  $ret;
    }


    private $_sanitationFunctionsFactory;
    private $_initialScope;
}

?>
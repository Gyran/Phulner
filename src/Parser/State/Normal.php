<?php
namespace Phulner\Parser\State;

use Phulner\Parser;
use Phulner\Parser\StateAbstract;
use Phulner\Parser\State;

class Normal extends StateAbstract {
    const CONFIG_START = "/^\/\*Phulner$/";

    public function __construct() {
        $this->_lines = "";
    }

    public function line($line, Parser $parser) {
        if (preg_match(self::CONFIG_START, $line)) {
            $this->flush($parser);

            $parser->changeState(new State\Config($parser));
        } else {
            $this->_lines = $this->_lines . $line;
        }
    }

    public function flush (Parser $parser) {
        $parser->addLines($this->_lines);
    }

    private $_lines;
}

?>

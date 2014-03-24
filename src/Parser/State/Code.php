<?php
namespace Phulner\Parser\State;

use Phulner\Parser;
use Phulner\Parser\StateAbstract;
use Phulner\Parser\State;

class Code extends StateAbstract {
    const CODE_END = "/^\/\*\/Phulner\*\/$/";

    public function __construct() {
        $this->_code = "";
    }

    public function line($line, Parser $parser) {
        if (preg_match(self::CODE_END, $line)) {
            $this->flush($parser);

            $parser->changeState(new State\Normal);
        } else {
            $this->_code = $this->_code . $line;
        }
    }

    public function flush (Parser $parser) {
        $parser->addCode($this->_code);
        $parser->saveVulnerability();
    }

    private $_code;
}

?>

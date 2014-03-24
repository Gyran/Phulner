<?php
namespace Phulner\Parser\State;

use Phulner\Parser;
use Phulner\Parser\StateAbstract;
use Phulner\Parser\State;

class Config extends StateAbstract {
    const CONFIG_END = "/^\*\/$/";

    public function __construct(Parser $parser) {
        $this->_config = "";
        $parser->initVulnerability();
    }

    public function line($line, Parser $parser) {
        if (preg_match(self::CONFIG_END, $line)) {
            $this->flush($parser);

            $parser->changeState(new State\Code);
        } else {
            $this->_config = $this->_config . $line;
        }
    }

    public function flush (Parser $parser) {
        $parser->addConfig($this->_config);
    }

    private $_config;
}

?>

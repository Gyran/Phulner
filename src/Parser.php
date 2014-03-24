<?php
namespace Phulner;

use Phulner\Parser\State;
use Phulner\Parser\StateAbstract;
use Phulner\File;
use Phulner\File\Part;

class Parser {
    public function parse($path) {
        $this->beforeParse();

        $this->_file = new File($path);
        $fh = fopen($path, "r");

        while (($line = fgets($fh)) !== false) {
            $this->_state->line($line, $this);
        }

        $this->_state->flush($this);
        return $this->_file;
    }

    public function beforeParse () {
        $this->changeState(new State\Normal);
        $this->_file = [];
    }

    public function changeState(StateAbstract $state) {
        $this->_state = $state;
    }

    public function addCode ($code) {
        $this->_vulnerabilityPart->setCode($code);
    }

    public function addConfig ($config) {
        $this->_vulnerabilityPart->setConfig(json_decode($config));
    }

    public function addLines ($lines) {
        $this->_file->addPart(new Part\Block($lines));
    }

    public function initVulnerability () {
        $this->_vulnerabilityPart = new Part\Vulnerability;
    }

    public function saveVulnerability () {
        $this->_file->addPart($this->_vulnerabilityPart);
     }

    private $_vulnerabilityPart;

    private $_state;
    private $_file;
    private $_vulnerabilities;
}

?>

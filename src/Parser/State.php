<?php
namespace Phulner\Parser;

interface State {
    public function line($line, Parser $parser);
}


?>

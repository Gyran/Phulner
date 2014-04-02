<?php
namespace Phulner\PhpParser;

class NodeDumper extends \PhpParser\NodeDumper {
    public function dump($node) {
        $r = "";

        try {
            $r = parent::dump($node);
        } catch (\Exception $e) {

        }

        return $r;
    }
}

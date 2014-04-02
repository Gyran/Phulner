<?php
namespace Phulner\PhpParser;

class Lexer extends \PhpParser\Lexer {
    public function getNextToken(&$value = null, &$startAttributes = null, &$endAttributes = null) {
        $tokenId = parent::getNextToken($value, $startAttributes, $endAttributes);

        if ($tokenId == \PHPParser\Parser::T_CONSTANT_ENCAPSED_STRING
            || $tokenId == \PHPParser\Parser::T_LNUMBER
            || $tokenId == \PHPParser\Parser::T_DNUMBER
        ) {
            // could also use $startAttributes, doesn't really matter here
            $endAttributes['originalValue'] = $value;
        }

        return $tokenId;
    }
}

?>

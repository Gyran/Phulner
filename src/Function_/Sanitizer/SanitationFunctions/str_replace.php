<?php
use Phulner\Function_\Sanitizer;
use Phulner\Function_\Sanitizer\Helpers;
use Phulner\Function_\Sanitizer\ReturnCallableHandler;
use PhpParser\Node\Expr\FuncCall;

return function () {
    $function = new Sanitizer("str_replace");

    // returned taint chain
    {
        $can = Helpers::can_always();
        $do = Helpers::return_taintFromArgument(2);
        $handler = new ReturnCallableHandler($can, $do);
        $function->addReturnedTaintHandler($handler);
    }

    // input tainted chain
    {
        $can = Helpers::can_always();
        $do = Helpers::input_argument(2);
        $handler = new ReturnCallableHandler($can, $do);
        $function->addTaintedInputHandler($handler);
    }

    // replace chain
    {
        // sanitation === NONE
        {
            $can = Helpers::can_sanitation("NONE");
            $do = Helpers::replace_returnArgument(2);
            $handler = new ReturnCallableHandler($can, $do);
            $function->addReplaceHandler($handler);
        }
        { // sanitation === INSUFFICIENT_ENCODING
            // make sure htmlspecialchars is called without ENT_QUOTES
            $can = Helpers::can_sanitation("INSUFFICIENT_ENCODING");
            $do = function (FuncCall $funcCall, $options) {
                return Helpers::replace_htmlspecialchars($funcCall->args[2]);
            };
            $handler = new ReturnCallableHandler($can, $do);
            $function->addReplaceHandler($handler);
        }
    }

    return $function;
}
?>

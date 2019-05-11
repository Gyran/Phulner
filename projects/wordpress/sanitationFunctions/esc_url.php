<?php
use Phulner\Function_\Sanitizer;
use Phulner\Function_\Sanitizer\ReturnCallableHandler;

use PhpParser\Node\Expr\FuncCall;

use Phulner\Function_\Sanitizer\Helpers;

return function () {
    $function = new Sanitizer("esc_url");

    // returned taint chain
    {
        $can = Helpers::can_always();
        $do = Helpers::return_noTaint();
        $handler = new ReturnCallableHandler($can, $do);
        $function->addReturnedTaintHandler($handler);
    }

    // input tainted chain
    {
        $can = Helpers::can_always();
        $do = Helpers::input_argument(0);
        $handler = new ReturnCallableHandler($can, $do);
        $function->addTaintedInputHandler($handler);
    }

    // replace chain
    {
        // sanitation === NONE
        {
            $can = Helpers::can_sanitation("NONE");
            $do = Helpers::replace_returnArgument(0);
            $handler = new ReturnCallableHandler($can, $do);
            $function->addReplaceHandler($handler);
        }
        { // sanitation === INSUFFICIENT_ENCODING
            // make sure htmlspecialchars is called without ENT_QUOTES
            $can = Helpers::can_sanitation("INSUFFICIENT_ENCODING");
            $do = function (FuncCall $funcCall, $options) {
                return Helpers::replace_htmlspecialchars($funcCall->args[0]);
            };
            $handler = new ReturnCallableHandler($can, $do);
            $function->addReplaceHandler($handler);
        }
    }

    return $function;
};

?>

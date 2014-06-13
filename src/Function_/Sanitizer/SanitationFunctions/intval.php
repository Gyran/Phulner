<?php
use Phulner\Function_\Sanitizer;
use Phulner\Function_\Sanitizer\Helpers;
use Phulner\Function_\Sanitizer\ReturnCallableHandler;
use PhpParser\Node\Expr\FuncCall;

return function () {
    $function = new Sanitizer("intval");

    // returned taint chain
    $function->addReturnedTaintHandler(Helpers::return_handler_always_noTaint());

    // input tainted chain
    $function->addTaintedInputHandler(helpers::input_handler_always_argument(0));

    // replace chain
    $function->addReplaceHandler(Helpers::replace_handler_none_returnArgument(0));

    return $function;
}

?>

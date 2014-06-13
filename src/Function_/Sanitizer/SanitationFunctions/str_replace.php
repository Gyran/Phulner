<?php
use Phulner\Function_\Sanitizer;
use Phulner\Function_\Sanitizer\Helpers;
use Phulner\Function_\Sanitizer\ReturnCallableHandler;
use PhpParser\Node\Expr\FuncCall;

return function () {
    $function = new Sanitizer("str_replace");

    // returned taint chain
    $function->addReturnedTaintHandler(Helpers::return_handler_always_argumentTaint(2));

    // input tainted chain
    $function->addTaintedInputHandler(Helpers::input_handler_always_argument(2));

    // replace chain
    $function->addReplaceHandler(Helpers::replace_handler_none_returnArgument(2));

    return $function;
}
?>

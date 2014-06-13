<?php
use Phulner\Function_\Sanitizer;
use Phulner\Function_\Sanitizer\Helpers;
use Phulner\Function_\Sanitizer\ReturnCallableHandler;
use PhpParser\Node\Expr\FuncCall;

return function () {
    $function = new Sanitizer("htmlspecialchars");

    // returned taint chain
    $can = Helpers::can_always();
    $do = function (FuncCall $funcCall, $options) {
        if (isset($funcCall->args[1]) &&
            Helpers::bitwiseOrContainsConst($funcCall->args[1]->value, "ENT_QUOTES")) {
            return [];
        }
        return $funcCall->args[0]->taint;
    };
    $handler = new ReturnCallableHandler($can, $do);
    $function->addReturnedTaintHandler($handler);
    // if we want sanitizer to be NONE, htmlspecialchars never returns taint
    $can = Helpers::can_sanitation("NONE");
    $do = Helpers::return_noTaint();
    $handler = new ReturnCallableHandler($can, $do);
    $function->addReturnedTaintHandler($handler);

    // input tainted chain
    $function->addTaintedInputHandler(Helpers::input_handler_always_argument(0));

    // replace chain
    $function->addReplaceHandler(Helpers::replace_handler_none_returnArgument(0));

    return $function;
}

?>

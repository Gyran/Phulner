<?php
use Phulner\Function_\Sanitizer;
use Phulner\Function_\Sanitizer\ReturnCallableHandler;

use PhpParser\Node\Expr\FuncCall;

use Phulner\Function_\Sanitizer\Helpers;

return function () {
    $function = new Sanitizer("esc_attr");

    // returned taint chain
    // return the taint from first argument
    $can = Helpers::can_always();
    $do = Helpers::return_taintFromArgument(0);
    $handler = new ReturnCallableHandler($can, $do);
    $function->addReturnedTaintHandler($handler);
    // unless from any of these outputs
    $can = Helpers::can_outputs(
        ["NONJS_NOQUOTES", "NONJS_SINGLEQUOTES", "NONJS_DOUBLEQUOTES"]);
    $do = Helpers::return_noTaint();
    $handler = new ReturnCallableHandler($can, $do);
    $function->addReturnedTaintHandler($handler);

    // input tainted chain
    $can = Helpers::can_always();
    $do = Helpers::input_argument(0);
    $handler = new ReturnCallableHandler($can, $do);
    $function->addTaintedInputHandler($handler);

    // replace chain
    // sanitation === NONE
    $can = Helpers::can_sanitation("NONE");
    $do = Helpers::replace_returnArgument(0);
    $handler = new ReturnCallableHandler($can, $do);
    $function->addReplaceHandler($handler);

    // sanitation === BLACKLIST
    /*$can = Helpers::can_sanitation_output("BLACKLIST", "NONJS_DOUBLEQUOTES");
    $do = function (FuncCall $funcCall, $options) {
        return Helpers::replace_blacklist('/[<>\(\)\: ]/', $funcCall->args[0]);
    };
    $handler = new ReturnCallableHandler($can, $do);*/
    $function->addReplaceHandler(Helpers::replace_handler_blacklist_nonjs_doublequotes());

    return $function;
};

?>

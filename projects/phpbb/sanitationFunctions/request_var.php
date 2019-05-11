<?php

use Phulner\Function_\Sanitizer;
use Phulner\Function_\Sanitizer\ReturnCallableHandler;

use PhpParser\Node;

use Phulner\Function_\Sanitizer\Helpers;

return function () {
    $function = new Sanitizer("request_var");

    // returned taint chain
    $function->addReturnedTaintHandler(Helpers::return_handler_always_noTaint());

    // input tainted chain
    $function->addTaintedInputHandler(helpers::input_handler_always_taint(["USER"]));

    // replace chain
    //// sanitation === NONE
    ////// POST
    $can = function (Node\Expr\FuncCall $funcCall, $options) {
        return (
            $options->input === "POST" &&
            $options->sanitation === "NONE"
            );
    };
    $do = function (Node\Expr\FuncCall $funcCall, $options) {
        $dim = $funcCall->args[0]->value;
        $var = Helpers::variable("_POST");
        $arrayDimFetch = Helpers::arrayDimFetch($var, $dim);

        return $arrayDimFetch;
    };
    $handler = new ReturnCallableHandler($can, $do);
    $function->addReplaceHandler($handler);
    ////// GET
    $can = function (Node\Expr\FuncCall $funcCall, $options) {
        return (
            $options->input === "GET" &&
            $options->sanitation === "NONE"
            );
    };
    $do = function (Node\Expr\FuncCall $funcCall, $options) {
        $dim = $funcCall->args[0]->value;
        $var = Helpers::variable("_GET");
        $arrayDimFetch = Helpers::arrayDimFetch($var, $dim);

        return $arrayDimFetch;
    };
    $handler = new ReturnCallableHandler($can, $do);
    $function->addReplaceHandler($handler);

    //// sanitation === BLACKLIST
    ////// POST
    $can = function (Node\Expr\FuncCall $funcCall, $options) {
        return (
            $options->input === "POST" &&
            $options->sanitation === "BLACKLIST" &&
            $options->output === "NONJS_DOUBLEQUOTES"
            );
    };
    $do = function (Node\Expr\FuncCall $funcCall, $options) {
        $dim = $funcCall->args[0]->value;
        $var = Helpers::variable("_POST");
        $arrayDimFetch = Helpers::arrayDimFetch($var, $dim);
        $arg = new Node\Arg($arrayDimFetch);

        return Helpers::replace_preg_replace('/[<>\(\)\: ]/', $arg);
    };
    $handler = new ReturnCallableHandler($can, $do);
    $function->addReplaceHandler($handler);


    return $function;
}

?>

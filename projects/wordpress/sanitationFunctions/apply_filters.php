<?php
use Phulner\Function_\Sanitizer;
use Phulner\Function_\Sanitizer\ReturnCallableHandler;

use PhpParser\Node\Expr\FuncCall;

use Phulner\Function_\Sanitizer\Helpers;

return function () {
    $function = new Sanitizer("apply_filters");

    // returned taint chain
    $function->addReturnedTaintHandler(Helpers::return_handler_always_noTaint());

    // input tainted chain
    $function->addTaintedInputHandler(helpers::input_handler_always_argument(1));

    // replace chain
    $function->addReplaceHandler(Helpers::replace_handler_none_returnArgument(1));

    $function->addReplaceHandler(Helpers::replace_handler_blacklist_normal_tag(1));
    $function->addReplaceHandler(Helpers::replace_handler_blacklist_url_all(1));

    $function->addReplaceHandler(Helpers::replace_handler_whitelist_url_all(1));

    $function->addReplaceHandler(Helpers::replace_handler_encoding_nonjs_singlequotes(1));

    return $function;
};

?>

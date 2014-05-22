Sanitation function
============
When injecting Cross Site Scripting vulnerabilities a Sanitation function describes how functions sanitize input and how taint is propagated. It consists of three chains: tainted input, returned taint and a replace chain. Each handler in the chain has two functions a `can`-function and a `do`-function. The signature for the functions are:
```PHP
bool function can(FuncCall $funcCall, stdClass $options);
mixed function do(FuncCall $funcCall, stdClass $options);
```
[FuncCall][php-parserFuncCall] is a node representing the function in the AST and options the instance options for the vulnerability that should be injected.

First `can` is called and if it returns `true` the `do` function is called and returned. If `can` returns false, the next handler in the chain is called until a handlers `can`-function returns true.

In the file [Helpers.php][helpers] some helper functions are defined that can be used as `can` and `do` functions.


Tainted Input
----------------
This chain determines if the input to the function contains taint.

Returned Taint
----------------
If the input to the chain contains taint, what taint it propagated to the return value.

Replace
----------------
How should this function call be replaced inject the vulnerability described in `$options`?
A tip here is to use different handlers for different combinations of `$options`

Example
----------------
An example of how `intval` is described with its sanitation function:
[Example of a Sanitation function][SanitationFunction-intval].
`intval` never returns any taint and the input is tainted if argument `0` is tainted. Depending on which type of sanitation and output `intval` is replaced in different ways.

 [php-parserFuncCall]: https://github.com/nikic/PHP-Parser/blob/master/lib/PhpParser/Node/Expr/FuncCall.php
 [SanitationFunction-intval]: ../src/Function_/Sanitizer/SanitationFunctions/intval.php
 [helpers]: ../src/Function_/Sanitizer/Helpers.php

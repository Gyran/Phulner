<?php



/*Phulner
{
    "identifier": "phpbb3",
    "inputs": ["STORED"],

    "initialScope": [
        {
            "name": "result",
            "type": "variable",
            "taint": ["USER"]
        }
    ]
}
*/
$result = trim(
    htmlspecialchars(
        str_replace(array("\r\n", "\r", "\0"), array("\n", "\n", ''), $result),
        ENT_COMPAT,
        'UTF-8'));
/*/Phulner*/

?>

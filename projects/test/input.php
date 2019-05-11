<?php

/*Phulner
{
    "identifier": "xss_htmlspecialchars_ent_quotes",
    "inputs": ["STORED", "GET"],

    "initialScope": [
        {
            "name": "unsafe",
            "type": "variable",
            "taint": ["USERsssssssss"]
        },
        {
            "name": "_GET",
            "type": "array",
            "taint": ["USERaaa"],
            "inherit": true,
            "keys": [
                {
                    "name": "arr",
                    "type": "array",
                    "taint": ["USER"],
                    "inherit": true,
                    "keys": [
                        {
                            "name": "wut",
                            "type": "variable",
                            "taint": ["WUTsss"]
                        }
                    ]
                }
            ]
        }
    ]
}
*/
$safe = htmlspecialchars($unsafe);
$wut = intval($safe);
//$wut = "wut";
//$_GET[$wut] = "wut";
//$_GET["wut"];
//$arr = $_GET["arr"];
//$safe = $arr[$_GET["wut"]];
//$wut = "hej";
//$safe = $_GET["arr"][$wut];
//$safe["wut"] = "hej";
//$safe["b"] = $_GET["arr"]["wut"];
//$safe["b"] = htmlspecialchars($_GET["arr"]["wut"], ENT_QUOTES);
/*/Phulner*/

/*Phulner
{
    "identifier": "xss_htmlspecialchars",
    "inputs": ["STORED"],

    "initialScope": [
        {
            "name": "unsafe",
            "type": "variable",
            "taint": ["USER"]
        }
    ]
}
*/
$safe = htmlspecialchars($unsafe);
/*/Phulner*/

/*Phulner
{
    "identifier": "xss_intval",

    "initialScope": [
        {
            "name": "unsafe",
            "type": "variable",
            "taint": ["user"]
        }
    ]
}
*/
$safe = intval($unsafe);
/*/Phulner*/

/*Phulner
{
    "identifier": "xss_remove",
    "method": "removeing"
}
*/
doThis("yeah!");
/*/Phulner*/

/*Phulner
{
    "identifier": "xss_plus",
    "initialScope": [
        {
            "name": "unsafe",
            "type": "variable",
            "taint": ["user"]
        }
    ]
}
*/
$safe1 = 0 - $unsafe;
$safe2 = $unsafe * 0;
/*/Phulner*/


$aaa = $bbb;

?>

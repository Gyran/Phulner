#!/usr/bin/php
<?php
require __DIR__ . "/vendor/autoload.php";

function stringColor($text, $color = "0") {
    echo "\033[", $color, "m", $text, "\033[0m";
}

$welcome = <<<'WELCOME'

 ██▓███   ██░ ██  █    ██  ██▓     ███▄    █ ▓█████  ██▀███
▓██░  ██▒▓██░ ██▒ ██  ▓██▒▓██▒     ██ ▀█   █ ▓█   ▀ ▓██ ▒ ██▒
▓██░ ██▓▒▒██▀▀██░▓██  ▒██░▒██░    ▓██  ▀█ ██▒▒███   ▓██ ░▄█ ▒
▒██▄█▓▒ ▒░▓█ ░██ ▓▓█  ░██░▒██░    ▓██▒  ▐▌██▒▒▓█  ▄ ▒██▀▀█▄
▒██▒ ░  ░░▓█▒░██▓▒▒█████▓ ░██████▒▒██░   ▓██░░▒████▒░██▓ ▒██▒
▒▓▒░ ░  ░ ▒ ░░▒░▒░▒▓▒ ▒ ▒ ░ ▒░▓  ░░ ▒░   ▒ ▒ ░░ ▒░ ░░ ▒▓ ░▒▓░
░▒ ░      ▒ ░▒░ ░░░▒░ ░ ░ ░ ░ ▒  ░░ ░░   ░ ▒░ ░ ░  ░  ░▒ ░ ▒░
░░        ░  ░░ ░ ░░░ ░ ░   ░ ░      ░   ░ ░    ░     ░░   ░
          ░  ░  ░   ░         ░  ░         ░    ░  ░   ░


WELCOME;

echo stringColor($welcome, "0;35");

if ($argc !== 2) {
    echo stringColor("usage: " . $argv[0] ." <inconfig>\n", "0;32");
    exit;
}

//$inconfigPath = getcwd() . "/" . $argv[1];
$inconfigPath = $argv[1];
$inconfig = json_decode(file_get_contents($inconfigPath));
if ($inconfig === null) {
    echo stringColor(sprintf("%s couldn't be parsed as JSON\n", $inconfigPath), "0;31");
    exit;
}

$generator = new Phulner\Generator();
$generator->fromConfig($inconfig);

$generator->generate();

?>

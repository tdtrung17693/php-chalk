<?php
$loader = require_once __DIR__ . '/../vendor/autoload.php';
$chalk = new TdTrung\Chalk\Chalk();

print $chalk->bold->green("Bold Green");
print "\n";
print $chalk->underscore->color220->bgColor20("Blink Foreground 220 Background 20");
print "\n";
print $chalk->rgb(200, 20, 100)->inverse("Inverse\n");

// Style nesting
print $chalk->red("Red then", $chalk->bold->green("bold and green then"), $chalk->reset("back to normal\n"));

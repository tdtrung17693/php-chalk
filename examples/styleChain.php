<?php
require_once __DIR__ . "/autoload.php";

print $chalk->bold->green("Bold Green");
print "\n";
print $chalk->italic->red("Italic Red");
print "\n";
print $chalk->strikethrough->color25("Strikethrough Color 25");
print "\n";
print $chalk->underscore->color70("Underscore Color 70");
print "\n";
print $chalk->blink->color150("Blink Color 150");
print "\n";
print $chalk->underscore->color220->bgColor20("Blink Foreground 220 Background 20");
print "\n";
print $chalk->rgb(200, 20, 100)->inverse("Inverse");

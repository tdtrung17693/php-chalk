<?php
require_once __DIR__ . "/autoload.php";

print $chalk->red("Red then", $chalk->bold->green("bold and green then"), $chalk->reset("back to normal\n"));
print $chalk->color93("Color 93", $chalk->bgColor124->color210("background color 124"), $chalk->blue("blue text"));

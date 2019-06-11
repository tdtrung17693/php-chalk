<?php
require_once __DIR__ . "/autoload.php";

print "\033[2J";
print "\nForeground:\n";

$executionStartTime = microtime(true);
for ($i = 0; $i < 256; ++$i) {
    $color = "color_{$i}";
    print $chalk->$color(" $i ");
    if ($i == 15 || ($i > 15 && $i % 21 == 0)) print "\n";
}
$elapsed = microtime(true) - $executionStartTime;

print "\n\nBackground:\n";

for ($i = 0; $i < 256; ++$i) {
    $color = "bgColor_{$i}";
    print $chalk->$color("  ");
    if ($i == 15 || ($i > 15 && $i % 21 == 0)) print "\n";
}

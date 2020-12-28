<?php

/**
 * This file errors example
 */

$a = rand(0, 1);
$b = rand(0, 1);

if (empty($a) || empty($b)) {
    echo 'Hello';
}

while (empty($a) || empty($b)) {
    echo 'Hello';
}

for ($i = 0; $i < 0 || empty($a); $i++) {
    echo 'hello';
}

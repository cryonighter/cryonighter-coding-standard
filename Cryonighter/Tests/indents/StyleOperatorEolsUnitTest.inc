<?php

// // ///

// operators
if (true) {
    if (true) {
        $c = 3;
    }
    $c = 4;
}
/**
 * comment example
 */
if (false) {
    $i = 6;
} elseif ($c == 4) {
    $c = 7;
} else {
    $c = 8;
}
/*
 * comment example
 */
if (false) {
    $i = 6;
} else {
    $c = 7;
}
$rows = [
    '1' => 1,
    '2' => 2,
    '3' => 3,
];
foreach ($rows as $key => $row) {
    // comment example
    return $row;
}
for ($i = 0; $i < 10; $i++) {
    /*
     * comment example
     */
    $c++;
}
while ($i <= 10) {
    // comment example
    $i++;
    
    return $c;
}
switch ($i) {
    case 0:
        echo "i равно 0";
        break;
    case 1:
        echo "i равно 1";
        break;
    case 2:
        echo "i равно 2";
        break;
}

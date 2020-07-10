<?php
/**
 * This file errors exaple
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Dontsov Dmitry ddontsov93@gmail.com
 */

// function
function functionExampleOne()
{
    // comment example
    
    return true;
}
// function
function functionExampleTwo()
{
    // cycle example 3
    do {
        
        return true;
    } while (0);
}

// operator
if (date('m') > 8) {
    
    return true;
} elseif (date('m') > 10) {
    // comment example
    
    return true;
}

$rows = array(
    '1' => 1,
    '2' => 2,
    '3' => 3,
);

// cycle example 1
foreach ($rows as $key => $row) {
    // comment example
    
    return $row;
}

// cycle example 2
for ($i=0; $i < 10; $i++) {
    /*
     * comment example
     */ 
    
    return true;
}

// cycle example 2
while ($i <= 10) {
    // comment example
    $i++;
    return true;
}

// exception example
try {
    // comment example

    return $i;
} catch (Exception $e) {
    // comment example

    return $i;
} finally {
    // comment example
    return $i;
}

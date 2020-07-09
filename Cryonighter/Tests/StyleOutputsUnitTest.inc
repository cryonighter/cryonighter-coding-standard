<?php
/**
 * This file errors exaple
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Dontsov Dmitry ddontsov93@gmail.com
 */

function functionName()
{
    // comment example
    return 1;
}
// cycle example 1
foreach ($variable as $key => $value) {
    // comment example
    
    return $variable;
}

// cycle example 2
for ($i=0; $i < 10; $i++) {
    /*
     * comment example
     */ 
    
    return $i;
}

// cycle example 2
while ($i <= 10) {
    
    return $i;
}

// cycle example 3
do {
    
    return $i;
} while ($i > 0);

// operator
if ($i>1) {
    
    return $i;
} else {
    // comment example

    return $i;
}

// exeption exaple
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
// comment example
return 1;

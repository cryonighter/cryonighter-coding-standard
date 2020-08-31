<?php

namespace Cryonighter\Tests\LineBreaks;

/**
 * This file errors example
 */

class Test
{

    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array
     */
    public function testFunction()
    {

        return [
            'elementOne',
        ];
    }

    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {

        $testing = $phpcsFile->testFunction();
        $msg = 'LineBreaks. More than one line-break';
        // generate error output
        
        return null;
    }
}

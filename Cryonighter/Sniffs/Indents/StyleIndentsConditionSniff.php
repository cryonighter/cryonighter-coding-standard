<?php

/**
 * This sniff prohibits the use of Perl style hash comments.
 *
 * An example of a hash comment is:
 *
 * <code>
 * if ($a = rand(0, 1)) {
 *    return true;
 * }
 * </code>
 */

namespace Cryonighter\Sniffs\Indents;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class StyleIndentsConditionSniff implements Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = [
        'PHP',
    ];

    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array
     */
    public function register()
    {
        return [
            T_IF,
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
        $tokens = $phpcsFile->getTokens();
        $errorStatus = false;
        $msg = 'Appropriation variable in condition;';
        // token cursor
        $cursor = $stackPtr;
        
        while ($tokens[$cursor]['type'] != 'T_OPEN_PARENTHESIS') {
            $cursor++;
        }
        
        if (!isset($tokens[$cursor]['parenthesis_closer'])) {
            return null;
        }

        while ($tokens[$cursor]['type'] != 'T_CLOSE_PARENTHESIS') {
            if (!isset($tokens[$cursor]['type'])) {
                return null;
            }
            
            $cursor++;

            if ($tokens[$cursor]['type'] == 'T_EQUAL') {
                $errorStatus = true;
            }
        }
        
        if ($errorStatus) {
            $phpcsFile->addError($msg, $stackPtr, 'Found');
        }
    }
}

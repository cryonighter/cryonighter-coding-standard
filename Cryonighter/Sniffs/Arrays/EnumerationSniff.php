<?php

/**
 * This sniff check last element comma in Array
 *
 * An example:
 *
 * <code>
 *   $supportedTokenizers = ['PHP',];
 * </code>
 */

namespace Cryonighter\Sniffs\Arrays;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class EnumerationSniff implements Sniff
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
            T_OPEN_SHORT_ARRAY,
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
        $adressPass = 0;
        $adressGash = 0;
        $msg = 'Arrays. Wrong used COMMA symbol;';
        $cursor = $stackPtr;
        $startLine = $tokens[$cursor]['line'];
        // One line record
        $endLine = $tokens[$cursor]['line'];

        if (isset($tokens[$cursor]['bracket_closer'])) {
            $cursor = $tokens[$cursor]['bracket_closer'];
            $endLine = $tokens[$cursor]['line'];
        }

        $cursor--;

        while ($tokens[$cursor]['type'] == 'T_WHITESPACE') {
            if (!isset($tokens[$cursor]['content'])) {
                break;
            }

            $cursor--;
        }

        if ($tokens[$cursor]['type'] != 'T_COMMA' && $startLine < $endLine) {
            // this is error
            $adressPass = $cursor;
            $errorStatus = true;
        } elseif ($startLine == $endLine && $tokens[$cursor]['type'] == 'T_COMMA') {
            // this is other error
            $adressGash = $cursor;
            $errorStatus = true;
        }

        if ($errorStatus) {
            $fix = $phpcsFile->addFixableError($msg, $stackPtr, 'Found');
            
            if ($fix === true) {
                if ($adressPass > 0) {
                    // add comma
                    $phpcsFile->fixer->addContent($adressPass, ',');
                }

                if ($adressGash > 0) {
                    // remove comma
                    $phpcsFile->fixer->replaceToken($cursor, '');
                }
            }
        }
    }
}

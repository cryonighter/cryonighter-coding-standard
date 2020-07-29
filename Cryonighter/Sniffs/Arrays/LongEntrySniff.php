<?php

/**
 * This sniff find and fix long arrays syntax
 *
 * An example:
 *
 * <code>
 *   $arr = array(1,1,2);
 * </code>
 */

namespace Cryonighter\Sniffs\Arrays;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class LongEntrySniff implements Sniff
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
            T_ARRAY,
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
        // all tokens invalid
        $errorStatus = true;
        $msg = 'Arrays. Allowed only short code;';
        
        $fix = $phpcsFile->addFixableError($msg, $stackPtr, 'Found');

        if ($fix === true) {
            $cursor = $stackPtr;
            // token cursor
            $tokenArray = $cursor;
            $tokenBracketOpen = $cursor;
            $tokenBracketClose = $cursor;

            if (isset($tokens[$tokenArray]['parenthesis_opener'])) {
                $tokenBracketOpen = $tokens[$tokenArray]['parenthesis_opener'];
            }

            $cursor++;

            if ($tokenBracketOpen > $cursor) {
                $phpcsFile->fixer->beginChangeset();

                while ($tokenBracketOpen < $cursor) {
                    $phpcsFile->fixer->replaceToken($cursor, '');
                    $cursor--;
                }
                
                $phpcsFile->fixer->replaceToken($cursor, '');
                $phpcsFile->fixer->endChangeset();
            }
            
            if (isset($tokens[$tokenArray]['parenthesis_closer'])) {
                $tokenBracketClose = $tokens[$tokenArray]['parenthesis_closer'];
            }
            
            $phpcsFile->fixer->beginChangeset();
            $phpcsFile->fixer->replaceToken($tokenBracketClose, ']');
            $phpcsFile->fixer->replaceToken($tokenBracketOpen, '[');
            $phpcsFile->fixer->replaceToken($stackPtr, '');
            $phpcsFile->fixer->endChangeset();
        }
    }
}

<?php

/**
 * This sniff find multi-line condition blocks.
 *
 * An example:
 *
 * <code>
 *   if (
 *       empty($a) ||
 *       empty($b)
 *   ) {
 *       echo 'Hello';
 *   }
 * </code>
 */

namespace Cryonighter\Sniffs\Conditions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class BodyInOneLineSniff implements Sniff
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
            T_WHILE,
            T_FOR,
            T_FOREACH,
            T_IF,
            T_ELSEIF,
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
        $msg = 'Conditions. Body condition in several lines;';
        // token cursor
        $cursor = $stackPtr;
        $startLine = $tokens[$cursor]['line'];

        // step 1: find cursor open body
        while ($tokens[$cursor]['type'] != 'T_OPEN_PARENTHESIS') {
            if (!isset($tokens[$cursor]['content'])) {
                return null;
            }

            $cursor++;
        }
        
        if (!isset($tokens[$cursor]['parenthesis_closer'])) {
            return null;
        }

        // step 2: find cursor open body
        $cursor = $tokens[$cursor]['parenthesis_closer'];
        $endLine = $tokens[$cursor]['line'];
        $cursorEnd = $cursor;
        $cursorStr = $stackPtr;

        // step 3: checking error
        if ($endLine > $startLine) {
            $errorStatus = true;
        }

        if ($errorStatus) {
            $fix = $phpcsFile->addFixableError($msg, $stackPtr, 'Found');

            if ($fix === true) {
                // delete line breaks
                $phpcsFile->fixer->beginChangeset();
                $cursor = $cursorEnd;

                while ($cursor > $cursorStr - 1) {
                    $content = $phpcsFile->fixer->getTokenContent($cursor);
                    
                    if ($content != nl2br($content)) {
                        $phpcsFile->fixer->replaceToken($cursor, '');
                    }

                    if ($tokens[$cursor]['type'] == 'T_WHITESPACE' && $tokens[$cursor]['length'] > 1) {
                        $phpcsFile->fixer->replaceToken($cursor, ' ');
                    }
                    
                    $cursor--;
                }

                $phpcsFile->fixer->endChangeset();
            }
        }
    }
}

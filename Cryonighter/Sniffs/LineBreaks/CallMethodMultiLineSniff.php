<?php

/**
 * This sniff find and fix one-lenes object methods
 *
 * An example of a hash comment is:
 *
 * <code>
 * $user = User::create()->update();
 * </code>
 */

namespace Cryonighter\Sniffs\LineBreaks;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class CallMethodMultiLineSniff implements Sniff
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
            T_OBJECT_OPERATOR,
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
        
        if (empty($tokens[$stackPtr + 1]['content'])) {
            return null;
        }

        if (empty($tokens[$stackPtr - 1]['content'])) {
            return null;
        }

        if ($tokens[$stackPtr - 1]['type'] != 'T_CLOSE_PARENTHESIS') {
            return null;
        }

        $msg = 'LineBreaks. One-line using method - "' . $tokens[$stackPtr + 1]['content'] . '"';
        // generate error output
        $fix = $phpcsFile->addFixableError($msg, $stackPtr, 'Found');
        
        if ($fix === true) {
            $cursor = $stackPtr;
            $indent = '    ';

            while ($tokens[$cursor]['line'] >= $tokens[$stackPtr]['line']) {
                if (!isset($tokens[$cursor]['content'])) {
                    return null;
                }

                $cursor--;
            }

            $cursor++;

            if ($tokens[$cursor]['type'] == 'T_WHITESPACE') {
                $indent .= $tokens[$cursor]['content'];
            }

            // $phpcsFile->fixer->addContentBefore($stackPtr, $indent);
            // $phpcsFile->fixer->addNewlineBefore($stackPtr);
            $current = $phpcsFile->fixer->getTokenContent($stackPtr);
            $phpcsFile->fixer->replaceToken($stackPtr, "\r\n" . $indent . $current);
        }
    }
}

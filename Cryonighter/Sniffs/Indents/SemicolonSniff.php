<?php

/**
 * This sniff find and fix spaces before semicolon
 *
 * example:
 *
 * <code>
 * $var = 0 ;
 * </code>
 */

namespace Cryonighter\Sniffs\Indents;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class SemicolonSniff implements Sniff
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
            T_SEMICOLON,
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
        $msg = 'Indents. Found before semicolon "%s" symbol;';
        // token cursor
        $cursor = $stackPtr - 1;
        $exemptions = [
            'T_WHITESPACE',
            'T_COMMENT',
            'T_DOC_COMMENT_OPEN_TAG',
            'T_DOC_COMMENT_STRING',
            'T_DOC_COMMENT_CLOSE_TAG',
        ];

        if (in_array($tokens[$cursor]['type'], $exemptions)) {
            $errorStatus = true;
        }

        if ($errorStatus) {
            $data = [];
            $data[] = trim($tokens[$stackPtr]['content']);
            $fix = $phpcsFile->addFixableError($msg, $stackPtr, 'Found', $data);

            if ($fix === true) {
                $phpcsFile->fixer->beginChangeset();
                $cursor = $stackPtr - 1;

                while (in_array($tokens[$cursor]['type'], $exemptions)) {
                    $phpcsFile->fixer->replaceToken($cursor, '');
                    $cursor--;
                }

                $phpcsFile->fixer->endChangeset();
            }
        }
    }
}

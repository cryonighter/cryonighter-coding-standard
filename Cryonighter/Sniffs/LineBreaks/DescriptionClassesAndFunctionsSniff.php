<?php

/**
 * This sniff find and fix empty more than one line-break
 *
 * An example of a hash comment is:
 *
 * <code>
 * public function register()
 * {
 *
 *     $tokens[] = T_RETURN;
 *     $tokens[] = T_YIELD;
 * </code>
 */

namespace Cryonighter\Sniffs\LineBreaks;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class DescriptionClassesAndFunctionsSniff implements Sniff
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
            T_OPEN_CURLY_BRACKET,
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
        $msg = 'LineBreaks. Extra line break at the beginning of classes or functions;';

        // is end file
        if (!isset($tokens[$stackPtr + 2]['content'])) {
            return null;
        }

        // this test for error
        if ($tokens[$stackPtr + 2]['content'] == nl2br($tokens[$stackPtr + 2]['content'])) {
            return null;
        }
        
        // is this sniff
        $cursor = $stackPtr;
        $line = $tokens[$cursor]['line'];
        $rules = [
            'class',
            'public',
            'protected',
            'private',
        ];

        // is this sniff
        while (!in_array($tokens[$cursor]['content'], $rules)) {
            if (!isset($tokens[$cursor]['content'])) {
                return null;
            }
            
            $cursor--;
        }

        // is this sniff
        $section = $line - $tokens[$cursor]['line'];
        
        // is this sniff
        if ($section > 1) {
            return null;
        }
        
        // generate error output
        $fix = $phpcsFile->addFixableError($msg, $stackPtr, 'Found');

        if ($fix == true) {
            $cursor = $stackPtr + 2;
            $phpcsFile->fixer->beginChangeset();
            $phpcsFile->fixer->replaceToken($cursor, '');
            $phpcsFile->fixer->endChangeset();
        }
    }
}

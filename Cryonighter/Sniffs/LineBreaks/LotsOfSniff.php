<?php

/**
 * This sniff find and fix empty more than one line-break
 *
 * An example of a hash comment is:
 *
 * <code>
 *   $a = 4;
 *
 *   $b = $a;
 * </code>
 */

namespace Cryonighter\Sniffs\LineBreaks;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class LotsOfSniff implements Sniff
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
            T_WHITESPACE,
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
        $msg = 'LineBreaks. More than one line-break';
        $rules = [
            'T_SWITCH',
            'T_IF',
            'T_WHILE',
            'T_FOREACH',
            'T_FOR',
            'T_DOC_COMMENT_OPEN_TAG',
            'T_RETURN',
        ];
        $classRules = [
            'T_USE',
            'T_NAMESPACE',
            'T_CLASS',
            'T_RETURN',
        ];
        
        // skip not clean line
        if ($tokens[$stackPtr]['column'] > 1) {
            if ($tokens[$stackPtr - 1]['type'] == 'T_CLOSE_CURLY_BRACKET') {
                return null;
            }

            if ($tokens[$stackPtr - 1]['type'] == 'T_DOC_COMMENT_CLOSE_TAG') {
                return null;
            }

            if (isset($tokens[$stackPtr + 2]['type'])) {
                if (in_array($tokens[$stackPtr + 2]['type'], $rules)) {
                    return null;
                }
            }

            if (isset($tokens[$stackPtr + 2]['type'])) {
                if (in_array($tokens[$stackPtr + 2]['type'], $classRules)) {
                    return null;
                }
            }

            if (isset($tokens[$stackPtr + 3]['type'])) {
                if (in_array($tokens[$stackPtr + 3]['type'], $rules)) {
                    return null;
                }
            }

            if (isset($tokens[$stackPtr + 4]['type'])) {
                if (in_array($tokens[$stackPtr + 4]['type'], $rules)) {
                    return null;
                }
            }

            if (isset($tokens[$stackPtr + 5]['type'])) {
                if (in_array($tokens[$stackPtr + 5]['type'], $rules)) {
                    return null;
                }
            }
        }

        // skip one-line tokens
        if ($tokens[$stackPtr]['content'] == nl2br($tokens[$stackPtr]['content'])) {
            return null;
        }

        // skip first file line token
        if (!isset($tokens[$stackPtr - 1]['content'])) {
            return null;
        }

        // skip last file line token
        if (!isset($tokens[$stackPtr + 1]['content'])) {
            return null;
        }

        // skip not-first space line tokens
        if ($tokens[$stackPtr - 1]['type'] == 'T_WHITESPACE' && $tokens[$stackPtr - 1]['column'] == 1) {
            if ($tokens[$stackPtr - 1]['content'] != nl2br($tokens[$stackPtr - 1]['content'])) {
                return null;
            }
        }

        // skip lonely line tokens
        if ($tokens[$stackPtr + 1]['type'] != 'T_WHITESPACE') {
            return null;
        }

        // skip lonely line tokens
        if ($tokens[$stackPtr + 1]['content'] == nl2br($tokens[$stackPtr + 1]['content'])) {
            return null;
        }
        
        // generate error output
        $fix = $phpcsFile->addFixableError($msg, $stackPtr, 'Found');

        if ($fix === true) {
            $cursor = $stackPtr;
            $cursor++;
            $phpcsFile->fixer->beginChangeset();

            while ($tokens[$cursor]['type'] == 'T_WHITESPACE') {
                if (!isset($tokens[$cursor]['content'])) {
                    break;
                }
                
                $phpcsFile->fixer->replaceToken($cursor, '');
                $cursor++;
            }

            $phpcsFile->fixer->endChangeset();
        }
    }
}

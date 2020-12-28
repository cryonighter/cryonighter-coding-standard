<?php

/**
 * This sniff finding misplaced spaces around common operators.
 *
 * An example of a hash comment is:
 *
 * <code>
 * $a  =  5;
 * </code>
 */

namespace Cryonighter\Sniffs\Indents;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class AroundShortOperatorsSniff implements Sniff
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
        $targets = [
            T_INLINE_THEN,
            T_INLINE_ELSE,
            T_STRING_CONCAT,
            T_INSTANCEOF,
            T_TRUE,
            T_FALSE,
            T_NULL,
        ];

        if (Tokens::$comparisonTokens) {
            $targets += Tokens::$comparisonTokens;
        }

        if (Tokens::$operators) {
            $targets += Tokens::$operators;
        }

        if (Tokens::$assignmentTokens) {
            $targets += Tokens::$assignmentTokens;
        }

        if (Tokens::$booleanOperators) {
            $targets += Tokens::$booleanOperators;
        }

        return array_unique($targets);
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
        $cursorLeft = $stackPtr - 1;

        if (!isset($tokens[$cursorLeft]['content'])) {
            return null;
        }

        $cursorRight = $stackPtr + 1;

        if (!isset($tokens[$cursorRight]['content'])) {
            return null;
        }

        $errorStatusBefore = false;
        $errorStatusAfter = false;
        $tokenContent = $tokens[$stackPtr]['content'];
        $msg = [
            'before' => 'Indents. Misplaced spaces before "' . $tokenContent . '";',
            'after' => 'Indents. Misplaced spaces after "' . $tokenContent . '";',
        ];
        // token cursor
        $cursor = $stackPtr;
        
        // This Sniff worked only indents
        if ($tokens[$cursorLeft]['type'] == 'T_WHITESPACE' && $tokens[$cursorLeft]['content'] != nl2br($tokens[$cursorLeft]['content'])) {
            return null;
        }

        // This Sniff worked only indents
        if ($tokens[$cursorRight]['type'] == 'T_WHITESPACE' && $tokens[$cursorRight]['content'] != nl2br($tokens[$cursorRight]['content'])) {
            return null;
        }

        if ($tokens[$cursorLeft]['type'] == 'T_WHITESPACE') {
            if ($tokens[$cursorLeft]['length'] > 1) {
                $errorStatusBefore = true;
            }
        }

        if ($tokens[$cursorRight]['type'] == 'T_WHITESPACE') {
            if ($tokens[$cursorRight]['length'] > 1) {
                $errorStatusAfter = true;
            }
        }

        if ($errorStatusAfter) {
            // create error
            $fixAfter = $phpcsFile->addFixableError($msg['after'], $stackPtr, 'Found');

            if ($fixAfter === true) {
                $phpcsFile->fixer->replaceToken($cursorRight, ' ');
            }
        }

        if ($errorStatusBefore) {
            // create error
            $fixBefore = $phpcsFile->addFixableError($msg['before'], $stackPtr, 'Found');
            
            if ($fixBefore === true) {
                $phpcsFile->fixer->replaceToken($cursorLeft, ' ');
            }
        }
    }
}

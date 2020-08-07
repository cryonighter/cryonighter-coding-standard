<?php

/**
 * This sniff find, create before and after blocks condition, cycle line Breaks
 *
 * An example of a hash comment is:
 *
 * <code>
 * $cursor--;
 * if (true) {
 *     $c = 2;
 *     if (true) {
 *         $c = 3;
 *     }
 *     $c = 4;
 * }
 * $cursor--;
 * </code>
 */

namespace Cryonighter\Sniffs\LineBreaks;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class OperatorEolsSniff implements Sniff
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
            T_SWITCH,
            T_IF,
            T_WHILE,
            T_FOREACH,
            T_FOR,
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
        $msg = [];
        // check before error
        $errorBeforeStatus = $this->checkBeforeError($tokens, $stackPtr);
        $cursorBegin = $this->findCursorBegin($tokens, $stackPtr);
        
        if ($cursorBegin === false) {
            $cursorBegin = $stackPtr;
        }
        
        // check after error
        $errorAfterStatus = $this->checkAfterError($tokens, $stackPtr);
        $cursorEnd = $this->findCursorEnd($tokens, $stackPtr);
        
        if ($errorAfterStatus) {
            $msg['after'] = 'LineBreaks. Missing empty line found after line: ' . trim($tokens[$cursorEnd]['line']);
        }

        if ($errorBeforeStatus) {
            $msg['before'] = 'LineBreaks. Missing empty line found before line: ' . trim($tokens[$cursorBegin]['line']);
        }

        if ($errorAfterStatus) {
            // create error
            $fixAfter = $phpcsFile->addFixableError($msg['after'], $cursorEnd, 'Found');

            if ($fixAfter === true) {
                $phpcsFile->fixer->addNewline($cursorEnd);
            }
        }

        if ($errorBeforeStatus) {
            // create error
            $fixBefore = $phpcsFile->addFixableError($msg['before'], $cursorBegin, 'Found');
            $errorStatusUpdate = $this->lineUpIsEmpty($tokens, $cursorBegin);
            
            if ($fixBefore === true && !$errorStatusUpdate) {
                $phpcsFile->fixer->addNewlineBefore($cursorBegin);
            }
        }
    }

    /**
     * Finding first block token
     *
     * @param array $token
     * @param int   $cursor
     *
     * @return int | bool
     */
    private function findCursorBegin($token, $cursor)
    {
        $result = $cursor;
        // first <-- token
        $cursor--;

        if ($token[$result]['column'] > 1) {
            $cursor--;
        }
        
        if ($token[$cursor]['type'] == 'T_COMMENT') {
            $result = $cursor;
        }
        
        // this token is begin sister block - not check
        if ($token[$cursor]['type'] == 'T_CLOSE_CURLY_BRACKET') {
            return false;
        }

        // long comment block
        if ($token[$cursor - 1]['type'] == 'T_DOC_COMMENT_CLOSE_TAG') {
            $cursor--;

            while ($token[$cursor]['type'] != 'T_DOC_COMMENT_OPEN_TAG') {
                $cursor--;
            }

            $result = $cursor;
        }

        // long broken comment block
        if (trim($token[$cursor - 1]['content']) == '*/') {
            $cursor--;

            while (stripos($token[$cursor]['content'], '/*') === false) {
                if (!isset($token[$cursor]['content'])) {
                    break;
                }
                
                $cursor--;
            }

            $result = $cursor;
        }

        return $result;
    }

    /**
     * Finding last block token
     *
     * @param array  $token
     * @param int    $cursor
     *
     * @return int
     */
    private function findCursorEnd($token, $cursor)
    {
        $fixLine = $token[$cursor]['line'];
        
        // code else and elseif blocks (T_IF)
        while ($token[$cursor]['type'] != 'T_OPEN_CURLY_BRACKET') {
            if (!isset($token[$cursor + 1]['type'])) {
                break;
            }

            $cursor++;
            
            if ($token[$cursor]['line'] > $fixLine) {
                break;
            }
        }

        if (isset($token[$cursor]['bracket_closer'])) {
            $cursor = $token[$cursor]['bracket_closer'];
            $cursor = $this->findCursorEnd($token, $cursor);
        } else {
            // rollback
            while ($token[$cursor]['type'] != 'T_CLOSE_CURLY_BRACKET') {
                if (!isset($token[$cursor - 1]['type'])) {
                    break;
                }

                $cursor--;
            }
        }
        
        return $cursor;
    }

    /**
     * Find after block error
     *
     * @param array $tokens
     * @param int   $stackPtr
     *
     * @return bool
     */
    private function checkAfterError($tokens, $stackPtr)
    {
        $result = false;
        $cursorEnd = $this->findCursorEnd($tokens, $stackPtr);
        $cursor = $cursorEnd;

        while (($tokens[$cursorEnd]['line'] + 1) >= $tokens[$cursor]['line']) {
            if (!isset($tokens[$cursor + 1]['content'])) {
                break;
            }
            
            $cursor++;
            
            if (($tokens[$cursorEnd]['line']) == $tokens[$cursor]['line']) {
                continue;
            }

            if (($tokens[$cursorEnd]['line'] + 1) < $tokens[$cursor]['line']) {
                break;
            }

            if ($tokens[$cursor]['type'] != 'T_WHITESPACE') {
                if ($tokens[$cursor]['type'] != 'T_CLOSE_CURLY_BRACKET') {
                    $result = true;
                }

                break;
            }
        }

        return $result;
    }

    /**
     * Find before block error
     *
     * @param array $tokens
     * @param int   $stackPtr
     *
     * @return bool
     */
    private function checkBeforeError($tokens, $stackPtr)
    {
        $result = false;
        $cursorBegin = $this->findCursorBegin($tokens, $stackPtr);

        if ($cursorBegin === false) {
            return $result;
        }

        $cursor = $cursorBegin;
        // check before empty line
        $cursor--;

        if (!isset($tokens[$cursor]['content'])) {
            return $result;
        }

        if ($tokens[$cursor]['type'] == 'T_WHITESPACE' && $tokens[$cursor]['column'] == 1) {
            return $result;
        }

        $cursor = $cursorBegin;

        while (($tokens[$cursorBegin]['line'] - 1) <= ($tokens[$cursor]['line'])) {
            if (!isset($tokens[$cursor]['content'])) {
                break;
            }
            
            $cursor--;

            if ($tokens[$cursor]['type'] != 'T_WHITESPACE') {
                if ($tokens[$cursor]['type'] != 'T_OPEN_CURLY_BRACKET') {
                    $result = true;
                }
                
                break;
            }
        }

        return $result;
    }

    /**
     * Check is empty line up
     *
     * @param array $token
     * @param int   $cursor CursorBegin
     *
     * @return bool
     */
    private function lineUpIsEmpty($token, $cursor)
    {
        $result = false;
        $fixLine = $token[$cursor]['line'] - 1;
        $exemptions = [
            'T_SWITCH',
            'T_WHILE',
            'T_FOREACH',
            'T_FOR',
            'T_IF',
            'T_ELSE',
            'T_ELSEIF',
        ];
        $cursor--;

        while ($token[$cursor]['type'] == 'T_WHITESPACE') {
            $cursor--;
        }

        if ($token[$cursor]['type'] != 'T_CLOSE_CURLY_BRACKET') {
            return $result;
        }

        if ($token[$cursor]['line'] < $fixLine) {
            return $result;
        }

        if (!isset($token[$cursor]['bracket_opener'])) {
            return $result;
        }

        $cursor = $token[$cursor]['bracket_opener'] - 1;

        while ($token[$cursor]['type'] == 'T_WHITESPACE') {
            $cursor--;
        }

        if (in_array($token[$cursor]['type'], $exemptions)) {
            $result = true;
            
            return $result;
        }

        if ($token[$cursor]['type'] != 'T_CLOSE_PARENTHESIS') {
            return $result;
        }

        if (!isset($token[$cursor]['parenthesis_owner'])) {
            return $result;
        }

        $cursor = $token[$cursor]['parenthesis_owner'];

        if (in_array($token[$cursor]['type'], $exemptions)) {
            $result = true;
        }

        return $result;
    }
}

<?php
/**
 * This sniff prohibits the use of Perl style hash comments.
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

namespace Cryonighter\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class StyleOperatorEolsSniff implements Sniff
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
        $tokens[] = T_SWITCH;
        $tokens[] = T_IF;
        $tokens[] = T_WHILE;
        $tokens[] = T_FOREACH;
        $tokens[] = T_FOR;

        return $tokens;
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
        $msg[] = '';        
        // check before error
        $cursorBegin = $this->findCursorBegin($tokens, $stackPtr);
        $errorBeforeStatus = $this->checkBeforeError($tokens, $stackPtr);        
        // check after error
        $errorAfterStatus = $this->checkAfterError($tokens, $stackPtr);
        $cursorEnd = $this->findCursorEnd($tokens, $stackPtr);
        
        if ($errorAfterStatus) {
            $msg['after'] = 'Missing empty line found after line: ' . trim(nl2br($tokens[$cursorEnd]['line']));
        }

        if ($errorBeforeStatus) {
            $msg['before'] = 'Missing empty line found before line: ' . trim(nl2br($tokens[$cursorBegin]['line']));
        }

        if ($errorBeforeStatus) {
            // create error
            $fixBefore = $phpcsFile->addFixableError($msg['before'], $stackPtr, 'Found');

            if ($fixBefore === true) {
                $phpcsFile->fixer->addNewlineBefore($cursorBegin);
            }

        }

        if ($errorAfterStatus) {
            // create error
            $fixAfter = $phpcsFile->addFixableError($msg['after'], $stackPtr, 'Found');

            if ($fixAfter === true) {
                $phpcsFile->fixer->addNewline($cursorEnd);
            }

        }
    
    }

    /**
     * @param  array $token
     * @param  int   $cursor
     *
     * @return int   $result begin code block
     */
    private function findCursorBegin($token, $cursor)
    {
        // result
        $result = $cursor;
        // first <-- token
        $cursor--;

        if ($token[$cursor]['type'] == 'T_COMMENT') {
            $result = $cursor;
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
                $cursor--;
            }

            $result = $cursor;
        }

        return $result;
    }

    /**
     * @param  array $token
     * @param  int   $cursor
     *
     * @return int   $result end code block
     */
    private function findCursorEnd($token, $cursor) {

        // this line
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
                $cursor--;
            }

        }
        
        return $cursor;
    }

    /**
     * find after block error
     * @param  array $token
     * @param  int   $cursor
     *
     * @return bool  $result end code block
     */
    private function checkAfterError($tokens, $stackPtr) {
        // default result
        $result = false;
        $cursorEnd = $this->findCursorEnd($tokens, $stackPtr);
        $cursor = $cursorEnd;

        while (($tokens[$cursorEnd]['line'] + 1) >= $tokens[$cursor]['line']) {
            $cursor++;
            
            if (!isset($tokens[$cursor]['type'])) {
                break;
            }
            
            if (($tokens[$cursorEnd]['line']) == $tokens[$cursor]['line']) {
                continue;
            }

            if (($tokens[$cursorEnd]['line'] + 1) < $tokens[$cursor]['line']) {
                break;
            }

            if ($tokens[$cursor]['type'] != 'T_WHITESPACE') {
                $result = true;
                break;
            }
        
        }

        return $result;
    }

    /**
     * find after block error
     * @param  array $token
     * @param  int   $cursor
     *
     * @return bool  $result end code block
     */
    private function checkBeforeError($tokens, $stackPtr) {
        // default result
        $result = false;
        $cursorBegin = $this->findCursorBegin($tokens, $stackPtr);
        $cursor = $cursorBegin;

        while (($tokens[$cursorBegin]['line'] - 1) <= ($tokens[$cursor]['line'])) {
            $cursor--;

            if ($tokens[$cursor]['type'] != 'T_WHITESPACE') {
                $result = true;
                break;
            }
        
        }

        return $result;
    }    
}

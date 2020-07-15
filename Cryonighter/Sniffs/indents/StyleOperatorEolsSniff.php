<?php
/**
 * This sniff prohibits the use of Perl style hash comments.
 *
 * An example of a hash comment is:
 *
 * <code>
 * $cursor--;
 * if (true) {
 *     return $cursor;
 * }
 * $cursor--;
 * </code>
 *
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
     * If TRUE, whitespace rules are not checked for blank lines.
     *
     * Blank lines are those that contain only whitespace.
     *
     * @var boolean
     */
    public $ignoreBlankLines = false;

    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array(int)
     */
    public function register()
    {
        $tokens[] = T_IF;
        $tokens[] = T_WHILE;
        //$tokens[] = T_DO;
        //$tokens[] = T_FOR;
        //$tokens[] = T_FOREACH;
        //$tokens[] = T_SWITCH;

        return $tokens;
    }

    /**
     * Processes this sniff, when one of its tokens is encountered.
     * @param File                       $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in the
     *                                               stack passed in $tokens.
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $errorBeforeStatus = true;
        $errorAfterStatus = true;
        $msg[] = '';
        
        // check before error
        $cursorBegin = $this->findCursorBegin($tokens, $stackPtr);
        $cursor = $cursorBegin;

        while ($tokens[$cursorBegin]['line'] >= ($tokens[$cursor]['line'] - 1)) {
            $cursor--;

            if ($tokens[$cursor]['type'] != 'T_WHITESPACE') {
                $msg[] = 'Missing empty line found before "%s";';
                $errorBeforeStatus = true;
                break;
            }
        
        }

        // check after error
        $cursorEnd = $this->findCursorEnd($tokens, $stackPtr);
        $cursor = $cursorEnd;

        while ($tokens[$cursorEnd]['line'] <= ($tokens[$cursor]['line'] + 1)) {
            $cursor++;

            if ($tokens[$cursor]['type'] != 'T_WHITESPACE') {
                $msg[] = 'Missing empty line found after "%s";';
                $errorBeforeStatus = true;
                break;
            }
        
        }

        $msg = implode("\r\n", $msg);
        // create error
        if ($errorBeforeStatus || $errorAfterStatus) {
            
            $data[] = trim($tokens[$cursorBegin]['content']);
            $data[] = trim($tokens[$cursorEnd]['content']);
            $phpcsFile->addError($msg, $stackPtr, 'Found', $data);
            // $fix = $phpcsFile->addFixableError($msg, $stackPtr, 'Found', $data);
        }
    
    }

    /**
     * @param  array $token
     * @param  int   $cursor
     * @return int   $result begin code block
     */
    private function findCursorBegin($token, $cursor)
    {
        // result
        $result = $cursor;
        // first <-- token
        $cursor--;
        
        if ($token[$cursor]['type'] = 'T_COMMENT') {
            $result = $cursor;
        }

        for ($i = 0; $i < 1; $i++) {
            
            if (
                $token[$cursor]['type'] == 'WHITESPACE' &&
                $token[$cursor]['content'] != nl2br($token[$cursor]['content'])
            ) {
                $cursor--;
            }
            
            $result = $cursor;
        }

        // long broken comment block
        if (trim($token[$cursor - 1]['content']) == '*/') {
            $cursor--;

            while (stripos($token[$cursor - 1]['content'], '/*') === false) {
                $cursor--;
            }

            $cursor--;
            $result = $cursor;
        }
        
        // long comment block
        if ($token[$cursor - 1]['type'] == 'T_DOC_COMMENT_CLOSE_TAG') {
            $cursor--;

            while ($token[$cursor - 1]['type'] != 'T_DOC_COMMENT_OPEN_TAG') {
                $cursor--;
            }

            $cursor--;
            $result = $cursor;
        }

        return $result;
    }

    /**
     * @param  array $token
     * @param  int   $cursor
     * @return int   $result end code block
     */
    private function findCursorEnd($token, $cursor) {

        // end --> token
        while ($token[$cursor]['type'] != 'T_OPEN_CURLY_BRACKET') {
            $cursor++;
        }
        
        $cursor = $token[$cursor]['bracket_closer'];

        // this line
        $fixLine = $token[$cursor]['line'];
    
        // code else and elseif blocks (T_IF)
        while ($token[$cursor]['line'] == $fixLine) {
            $cursor++;
            
            if (
                $token[$cursor]['line'] == $fixLine &&
                $token[$cursor]['type'] == 'T_OPEN_CURLY_BRACKET'
            ) {
                $cursor = $token[$cursor]['bracket_closer'];
                $cursor = $this->findCursorEndSunblock($token, $cursor);
                break;
            }

        }

        // $cursor--;

        while ($token[$cursor]['type'] != 'T_CLOSE_CURLY_BRACKET') {
            $cursor--;
        }

        return $cursor;
    }

    /**
     * check is dream inside dream inside dream level three inside... fuck shut it!
     * @param  array $token
     * @param  int   $cursor
     * @return int   $result end code block
     */
    private function findCursorEndSunblock($token, $cursor) {
        // this line
        $fixLine = $token[$cursor]['line'];
        // this is end or begin other block    
        while ($token[$cursor]['line'] == $fixLine) {
            $cursor++;
            
            if (
                $token[$cursor]['line'] == $fixLine &&
                $token[$cursor]['type'] == 'T_OPEN_CURLY_BRACKET'
            ) {
                $cursor = $this->findCursorEnd($token, $cursor);
                break;
            }

        }

        while ($token[$cursor]['type'] != 'T_CLOSE_CURLY_BRACKET') {
            $cursor--;
        }

        return $cursor;
    }

}

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
        $errorBeforeStatus = false;
        $errorAfterStatus = true;
        $msg[] = '';
        
        // check before error
        $cursorBegin = $this->findCursorBegin($tokens, $stackPtr);
        
        if (
            $tokens[$cursorBegin-2]['type'] != 'T_WHITESPACE' &&
            $tokens[$cursorBegin-2]['content'] == nl2br($tokens[$cursorBegin-2]['content'])
        ) {
            $msg[] = 'Missing empty line found before "%s";';
            $errorBeforeStatus = true;
        }

        // check after error
        $cursorEnd = $this->findCursorEnd($tokens, $stackPtr);

        

        $msg = implode("\r\n", $msg);
        // debug
        $error[] = $msg;
        // $error[] = ($this->findCursorBegin($tokens, $stackPtr));
        
        $error[] = var_export($tokens[$stackPtr]['line'], true);
        $error[] = var_export($tokens[$cursorEnd]['line'], true);
        $error = implode("\r\n___________\r\n", $error);


        

        // create error
        if ($errorBeforeStatus || $errorAfterStatus) {
            $data[] = trim($tokens[$stackPtr]['content']);
            $phpcsFile->addError($msg, $stackPtr, 'Found', $data);
            // $phpcsFile->addError($msg, $stackPtr, 'Found', $data);
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
        // code else and elseif blocks
        while ($token[$cursor]['type'] != 'T_CLOSE_CURLY_BRACKET') {
            $cursor++;
        }

        // this line
        $fixLine = $token[$cursor]['line'];
        $cursor++;

        // this is end or begin other block    
        while ($token[$cursor]['line'] == $fixLine) {
            $cursor++;
            
            if (
                $token[$cursor]['line'] == $fixLine &&
                $token[$cursor]['type'] == 'T_OPEN_CURLY_BRACKET'
            ) {
                $cursor = $this->findCursorEndSublock($token, $cursor);
            }

        }

        return $cursor - 1;
    }

    /**
     * rererererererererererecursisision
     * @param  array $token
     * @param  int   $cursor
     * @return int   $result end code block
     */
    private function findCursorEndSublock($token, $cursor) {
        // return $cursor;
        // end --> token
        // this line
        // code else and elseif blocks
        while ($token[$cursor]['type'] != 'T_CLOSE_CURLY_BRACKET') {
            $cursor++;
        }

        $fixLine = $token[$cursor]['line'];
        $cursor++;

        // this is end or begin other block    
        while ($token[$cursor]['line'] == $fixLine) {
            $cursor++;
            
            if (
                $token[$cursor]['line'] == $fixLine &&
                $token[$cursor]['type'] == 'T_OPEN_CURLY_BRACKET'
            ) {
                $cursor = $this->findCursorEndSublock($token, $cursor);
            }

        }

        return $cursor;  

    }

}

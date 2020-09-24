<?php

/**
 * This sniff checking and fixed blank lines at the end of the annotation block
 * An example of a hash comment is:
 *
 * <code>
 *  * some tag
 *  *
 *  *'/'
 * </code>
 */

namespace Cryonighter\Sniffs\Comments;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class DrainsInEndPhpDocSniff implements Sniff
{
    /**
    * The file being scanned.
    *
    * @var File|null
    */
    private $phpcsFile = null;

    /**
     * The position of the current token in the stack passed in $tokens.
     *
     * @var int
     */
    private $stackPtr = 1;

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
            T_DOC_COMMENT_CLOSE_TAG,
        ];
    }

    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $this->phpcsFile = $phpcsFile;
        $this->tokens = $this->phpcsFile->getTokens();
        $this->stackPtr = $stackPtr;
        $this->mainLoop();
    }

    /**
     * Encapsulation from external recursions
     *
     * @return void
     */
    private function mainLoop()
    {
        $cursor = $this->stackPtr;
        // Tokens used for parsing doc blocks.
        $phpDocTokens = [
            'T_DOC_COMMENT_STAR',
            'T_DOC_COMMENT_WHITESPACE',
            'T_DOC_COMMENT_OPEN_TAG',
            'T_DOC_COMMENT_CLOSE_TAG',
        ];
        
        // look at the beginning of the previous line
        while (trim($this->tokens[$cursor]['content']) != '*') {
            // foolproof
            if (!isset($this->tokens[$cursor]['content'])) {
                return null;
            }

            if (!in_array($this->tokens[$cursor]['type'], $phpDocTokens)) {
                return null;
            }

            $cursor--;
        }

        while ($this->tokens[$cursor]['content'] != nl2br($this->tokens[$cursor]['content'])) {
            // foolproof
            if (!isset($this->tokens[$cursor]['content'])) {
                return null;
            }

            if ($this->tokens[$cursor]['type'] != 'T_DOC_COMMENT_WHITESPACE') {
                $this->generateError($cursor);
                
                return null;
            }

            $cursor--;
        }

        // its not previos line
        if ($this->tokens[$cursor]['line'] >= $this->tokens[$this->stackPtr]['line']) {
            $this->generateError($cursor, '', false);
            
            return null;
        }

       // generate error output
        $this->generateError($cursor);
    }

    /**
     * generate error output
     *
     * @param string $msg
     * @param int    $cursor
     * @param bool   $fixable enable fix functions
     *
     * @return null
     */
    private function generateError($cursor, $msg = '', $fixable = true)
    {
        // default error
        if (empty($msg)) {
            $msg = 'Comments. There should be no blank lines at the end of the block.';
        }

        // no auto-fixableble error
        if ($fixable === false) {
            $this->phpcsFile->addError($msg, $cursor, 'Found');
            
            return null;
        }

        $fix = $this->phpcsFile->addFixableError($msg, $cursor, 'Found');
        $this->fixThisError($cursor, $fix);
    }

    /**
     * We try to automatically resolve errors
     *
     * @param boolean  $fix Variable describing the status of the error solution
     * @param int      $cursor
     *
     * @return null    Early exit from the procedure
     */
    private function fixThisError($cursor, $fix = false)
    {
        // Skip resolved problem
        if ($fix !== true) {
            return null;
        }

        // This is fixer!!!
        $this->phpcsFile->fixer->beginChangeset();

        // clean line
        for ($stack = $this->stackPtr - 1; $stack >= $cursor; $stack--) {
            // foolproof
            if (!isset($this->tokens[$stack]['content'])) {
                break;
            }

            // delete block tokens part
            $this->phpcsFile->fixer->replaceToken($stack, '');
        }

        $this->phpcsFile->fixer->endChangeset();
    }
}

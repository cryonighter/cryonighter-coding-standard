<?php

/**
 * The first line of the php file contains only the <? Php
 * operator and nothing else (including comments),
 * and is also separated by an additional line break
 * from the rest of the code.
 *
 * An example of a hash structure is:
 *
 * <code>
 * <?
 * namespace Examle\next\next
 * </code>
 */

namespace Cryonighter\Sniffs\Structure;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class StartOfFileSniff implements Sniff
{
    /**
    * The file being scanned.
    *
    * @var File|null
    */
    private $phpcsFile = null;

    /**
    * Array of tokens found in the scanned file
    *
    * @var array|null
    */
    private $tokens = null;

    /**
     * The position of the current token in the stack passed in $tokens.
     *
     * @var int
     */
    private $stackPtr = 0;

    /**
     * Error correction algorithm type
     *
     * @var int
     */
    private $errType = 0;

    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = [
        'PHP',
    ];

    /**
     * Returns error codes with decryptions.
     * P/s this is the correct way to declare arrays!
     *
     * @return array
     */
    private function errorTypeDecoding()
    {
        return [
            0 => ' in the design of the beginning of the script 0',
            1 => '. Open tag not at the beginning of the file *.php 1',
            2 => '. Missing blank line after tag "<?php" 2',
            3 => '. Extra indentation on the 3rd line 3',
        ];
    }

    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array
     */
    public function register()
    {
        return [
            T_OPEN_TAG,
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

        // open tag not at the beginning of the file
        if ($cursor !== 0) {
            $this->errType = 1;
            // create error
            $this->generateError($cursor);
            
            return null;
        }

        $cursor += 2;

        // Minors are not allowed to do complex recursions
        if (!isset($this->tokens[$cursor]['content'])) {
            return null;
        }

        // Checking for errors of the 2nd and 3rd types
        if ($this->tokens[$cursor]['line'] == 3 && $this->tokens[$cursor]['column'] == 1) {
            // Type 3 error checking
            if ($this->tokens[$cursor]['type'] == 'T_WHITESPACE') {
                $this->errType = 3;
                // create error
                $this->generateError($cursor);
                
                return null;
            }
            
            // no more errors
            return null;
        }

        // create type 2 error
        $this->errType = 2;
        $this->generateError($cursor);
        
        return null;
    }

    /**
     * generate error output
     *
     * @param string $msg
     * @param int    $cursor
     *
     * @return null
     */
    private function generateError($cursor, $msg = '')
    {
        // To use protection 'fore I bit into your forbidden fruit
        if (!isset($this->errorTypeDecoding()[$this->errType])) {
            $msg = 'Structure. Unknown error. ' . get_class($this) . ';';
            $this->phpcsFile->addError($msg, $cursor, 'Found');
        }

        // default error
        if (empty($msg)) {
            $msg = 'Structure. Error' . $this->errorTypeDecoding()[$this->errType] . ';';
            
            // no auto-fixableble error
            if ($this->errType === 0) {
                $this->phpcsFile->addError($msg, $cursor, 'Found');

                return null;
            }
        }

        $fix = $this->phpcsFile->addFixableError($msg, $cursor, 'Found');
        $this->fixThisError($this->stackPtr, $fix);
    }

    /**
     * We try to automatically resolve errors
     *
     * @param boolean $fix Variable describing the status of the error solution
     * @param int     $cursor
     *
     * @return null    Early exit from the procedure
     */
    private function fixThisError($cursor, $fix = false)
    {
        // Skip resolved problem
        if ($fix !== true) {
            return null;
        }

        // Open tag not at the beginning of the file *.php
        if ($this->errType === 1) {
            // move the main token to the topmost position
            $this->phpcsFile->fixer->beginChangeset();

            while ($cursor !== 0) {
                // foolproof
                if (!isset($this->tokens[$cursor - 1]['content'])) {
                    break;
                }

                $cursor--;
                // delete block tokens part
                $this->phpcsFile->fixer->replaceToken($cursor, '');
            }

            $this->phpcsFile->fixer->endChangeset();
        }

        // extra indentation on the 3rd line
        if ($this->errType === 3) {
            $cursor = $this->stackPtr + 2;
            $this->phpcsFile->fixer->beginChangeset();
            $this->phpcsFile->fixer->replaceToken($cursor, '');
            $this->phpcsFile->fixer->endChangeset();
        }

        // missing blank line after main tag
        if ($this->errType === 2) {
            $cursor = $this->stackPtr;
            
            if ($cursor !== 0) {
                return null;
            }

            if ($this->tokens[$cursor + 1]['content'] === nl2br($this->tokens[$cursor + 1]['content'])) {
                $this->phpcsFile->fixer->beginChangeset();
                $content = $this->tokens[$cursor]['content'];
                $content = trim($content);
                $this->phpcsFile->fixer->replaceToken($cursor, $content . "\r\n");
                $this->phpcsFile->fixer->endChangeset();
            }
            
            return null;
        }

        // That's a hard Vicodin to swallow, so I scrap these
        return null;
    }
}

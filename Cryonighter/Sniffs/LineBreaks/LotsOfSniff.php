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

        // skip one-line tokens
        if ($this->tokens[$this->stackPtr]['content'] === nl2br($this->tokens[$this->stackPtr]['content'])) {
            return null;
        }

        // skip not clean line
        if ($this->tokens[$this->stackPtr]['column'] > 1) {
            // is not empty line
            if ($this->tokens[$this->stackPtr]['content'] !== nl2br($this->tokens[$this->stackPtr]['content'])) {
                return null;
            }

            if ($this->tokens[$this->stackPtr - 1]['type'] == 'T_CLOSE_CURLY_BRACKET') {
                return null;
            }

            if ($this->tokens[$this->stackPtr - 1]['type'] == 'T_DOC_COMMENT_CLOSE_TAG') {
                return null;
            }

            if (isset($this->tokens[$this->stackPtr + 2]['type'])) {
                if (in_array($this->tokens[$this->stackPtr + 2]['type'], $rules)) {
                    return null;
                }
            }

            if (isset($this->tokens[$this->stackPtr + 2]['type'])) {
                if (in_array($this->tokens[$this->stackPtr + 2]['type'], $classRules)) {
                    return null;
                }
            }

            if (isset($this->tokens[$this->stackPtr + 3]['type'])) {
                if (in_array($this->tokens[$this->stackPtr + 3]['type'], $rules)) {
                    return null;
                }
            }

            if (isset($this->tokens[$this->stackPtr + 4]['type'])) {
                if (in_array($this->tokens[$this->stackPtr + 4]['type'], $rules)) {
                    return null;
                }
            }

            if (isset($this->tokens[$this->stackPtr + 5]['type'])) {
                if (in_array($this->tokens[$this->stackPtr + 5]['type'], $rules)) {
                    return null;
                }
            }
        }

        // skip first file line token
        if (!isset($this->tokens[$this->stackPtr - 1]['content'])) {
            return null;
        }

        // skip last file line token
        if (!isset($this->tokens[$this->stackPtr + 1]['content'])) {
            return null;
        }

        // skip not-first space line tokens
        if ($this->tokens[$this->stackPtr - 1]['type'] == 'T_WHITESPACE' && $this->tokens[$this->stackPtr - 1]['column'] == 1) {
            if ($this->tokens[$this->stackPtr - 1]['content'] !== nl2br($this->tokens[$this->stackPtr - 1]['content'])) {
                return null;
            }
        }

        // skip lonely line tokens
        if ($this->tokens[$this->stackPtr + 1]['type'] != 'T_WHITESPACE') {
            return null;
        }

        // skip lonely line tokens
        if ($this->tokens[$this->stackPtr + 1]['content'] == nl2br($this->tokens[$this->stackPtr + 1]['content'])) {
            return null;
        }
        
        // generate error output
        $fix = $this->generateError($this->stackPtr, $msg);
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
        // no auto-fixableble error
        if (empty($cursor)) {
            $msg = 'LineBreaks. Unknown error. ' . get_class($this) . ';';
            $this->phpcsFile->addError($msg, 0, 'Found');

            return null;
        }

        // no auto-fixableble error
        if (empty($msg)) {
            $msg = 'LineBreaks. Unknown error. ' . get_class($this) . ';';
            $this->phpcsFile->addError($msg, $cursor, 'Found');

            return null;
        }

        $fix = $this->phpcsFile->addFixableError($msg, $this->stackPtr, 'Found');
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

        $cursor = $this->stackPtr;
        $cursor++;
        $this->phpcsFile->fixer->beginChangeset();

        while ($this->tokens[$cursor]['type'] == 'T_WHITESPACE' && $this->tokens[$cursor]['content'] !== nl2br($this->tokens[$cursor]['content'])) {
            if (!isset($this->tokens[$cursor]['content'])) {
                break;
            }
            
            $this->phpcsFile->fixer->replaceToken($cursor, '');
            $cursor++;
        }

        $this->phpcsFile->fixer->endChangeset();
    }
}

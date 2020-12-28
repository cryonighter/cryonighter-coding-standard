<?php

/**
 * This Sniff checks and corrects every line in the %param annotation block,
 * for it is true when it contains the annotation itself, the variable type and its name.
 * In this case, the size of the indentation of the variable
 * from the annotation must be equal to the length of the longest type name + 1 character.
 * An example of a hash comment is:
 *
 * <code>
 *  * %param File                    $phpcsFile The file being scanned.
 *  * %param int                         $stackPtr  The position of the current token
 *  *'/'
 * </code>
 */

namespace Cryonighter\Sniffs\Comments;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class ValidParamTagSniff implements Sniff
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
            T_DOC_COMMENT_TAG,
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
        
        // Exclude all other tags
        if ($this->tokens[$cursor]['content'] !== '@param') {
            return null;
        }
        
        // no pain, not rape mind
        $cursor--;

        // lift cursor for line up
        while ($this->tokens[$cursor]['line'] != $this->tokens[$this->stackPtr]['line'] - 2) {
            // foolproof
            if (!isset($this->tokens[$cursor]['content'])) {
                return null;
            }
            
            // This tag must be the first in the group
            if ($this->tokens[$cursor]['content'] === '@param') {
                return null;
            }

            $cursor--;
        }

        // constitutional amendments
        $cursor = $this->stackPtr;
        // Tokens used for parsing doc blocks.
        $phpDocTokens = [
            'T_DOC_COMMENT_STAR',
            'T_DOC_COMMENT_WHITESPACE',
            'T_DOC_COMMENT_TAG',
            'T_DOC_COMMENT_OPEN_TAG',
            'T_DOC_COMMENT_CLOSE_TAG',
            'T_DOC_COMMENT_STRING',
        ];
        // create array
        $commentTags = [];
        // If the length of the penis was a sign of high origin, then the mule would become the ruler of the world. (c)
        $perfectSize = 0;
        // key for commentTags array
        $commentTagsKey = 0;

        // looking for all matching tags moving forward to the end of the block
        while ($this->tokens[$cursor]['type'] != 'T_DOC_COMMENT_CLOSE_TAG') {
            // foolproof
            if (!isset($this->tokens[$cursor]['content']) || !isset($this->tokens[$cursor + 2]['content'])) {
                return null;
            }

            // break php doc
            if (!in_array($this->tokens[$cursor]['type'], $phpDocTokens)) {
                return null;
            }

            // this is tag... but not my
            if ($this->tokens[$cursor]['type'] == 'T_DOC_COMMENT_TAG' && $this->tokens[$cursor]['content'] !== '@param') {
                // is not my tag
                break;
            }

            // saving up currect tag content length
            if ($this->tokens[$cursor]['content'] === '@param') {
                $commentTags[$commentTagsKey]['stack'] = $cursor + 2;
                $commentTags[$commentTagsKey]['line'] = $this->tokens[$cursor]['line'];
                $commentTags[$commentTagsKey]['column'] = $this->tokens[$cursor]['column'];
                
                // scanning next token
                if ($this->tokens[$cursor + 1]['content'] !== ' ') {
                    $msg = 'Comments. There must be one space after the @param tag.';
                }

                // scanning next token
                if ($this->tokens[$cursor + 2]['type'] != 'T_DOC_COMMENT_STRING') {
                    $msg = 'Comments. Data type not specified.';
                    $this->generateError($cursor, $msg);
                }
                
                $commentTags[$commentTagsKey]['content'] = $this->tokens[$cursor + 2]['content'];
                $commentTags[$commentTagsKey]['typesize'] = stripos($this->tokens[$cursor + 2]['content'], ' ');
                $commentTags[$commentTagsKey]['typeallsize'] = $commentTags[$commentTagsKey]['typesize'];
                
                // this length
                while ($this->tokens[$cursor + 2]['content'][$commentTags[$commentTagsKey]['typeallsize']] === ' ') {
                    $commentTags[$commentTagsKey]['typeallsize']++;
                }

                // What’s the ideal penis length?
                if ($commentTags[$commentTagsKey]['typesize'] !== false && $commentTags[$commentTagsKey]['typesize'] > $perfectSize) {
                    $perfectSize = $commentTags[$commentTagsKey]['typesize'];
                }

                $commentTagsKey++;
            }

            $cursor++;
        }

        $perfectSize++;

        // find fixable errors
        foreach ($commentTags as $row) {
            if ($row['typeallsize'] !== $perfectSize) {
                // generate error output
                $row['perfectSize'] = $perfectSize;
                $this->generateError($cursor, '', $row);
            }
        }

        return null;
    }

    /**
     * generate error output
     *
     * @param string $msg
     * @param int    $cursor
     * @param array  $brokenTag - tag for error
     *
     * @return null
     */
    private function generateError($cursor, $msg = '', $brokenTag = [])
    {
        // default error
        if (empty($msg)) {
            $msg = 'Comments. Incorrect @param annotation plot.';
        }

        // No auto-fixableble error
        if (!isset($brokenTag['stack'])) {
            $this->phpcsFile->addError($msg, $cursor, 'Found');
            
            return null;
        }

        $cursor = $brokenTag['stack'];
        $fix = $this->phpcsFile->addFixableError($msg, $cursor, 'Found');
        $this->fixThisError($cursor, $fix, $brokenTag);
    }

    /**
     * We try to automatically resolve errors
     *
     * @param boolean $fix Variable describing the status of the error solution
     * @param int     $cursor
     * @param array   $brokenTag - tag for error
     *
     * @return null    Early exit from the procedure
     */
    private function fixThisError($cursor, $fix = false, $brokenTag = [])
    {
        // Skip resolved problem
        if ($fix !== true) {
            return null;
        }

        $this->phpcsFile->fixer->beginChangeset();
        // This is fixer!!!
        $content = $brokenTag['content'];
        $content = explode(' ', $content);
        $brokenTag['perfectSize'] -= strlen($content[0]);
        $shortage = false;
        $addWhitespace = [];
        $cnt = -1;

        foreach ($content as $key => $symbol) {
            // skip the first literature
            if ($cnt === -1) {
                $cnt++;
                continue;
            }

            $cnt++;
            // skip all literation after whitespace
            if ($shortage === true && $cnt > $brokenTag['perfectSize']) {
                break;
            }

            // change fix type
            if (!empty($symbol) && $cnt < $brokenTag['perfectSize']) {
                $shortage = true;
            }

            // Add an unworthy whitespace character
            if ($shortage === true && $cnt !== $brokenTag['perfectSize']) {
                while ($cnt < $brokenTag['perfectSize']) {
                    array_splice($content, $key, 0, '');
                    $cnt++;
                }

                break;
            }

            // Removing the extra whitespace character
            if (empty($symbol) && $cnt >= $brokenTag['perfectSize']) {
                unset($content[$key]);
            }
        }

        $content = implode(' ', $content);
        // apply changes
        $this->phpcsFile->fixer->replaceToken($brokenTag['stack'], $content);
        $this->phpcsFile->fixer->endChangeset();
    }
}

<?php

/**
 * This sniff prohibits the use of Perl style hash comments.
 *
 * An example of a hash comment is:
 *
 * <code>
 *
 * This file errors example
 *
 *
 * @param float $a
 *
 * @return bool
 *
 * </code>
 */

namespace Cryonighter\Sniffs\Comments;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class PhpDocCleanerSniff implements Sniff
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
            T_DOC_COMMENT_TAG,
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
        $errorStatus = false;
        $msg = 'Comments. Incorrect parameter in PhpDoc - "';
        // token cursor
        $cursor = $stackPtr;
        $tagsIncorrect = [
            '@api',
            '@author',
            '@category',
            '@copyright',
            '@example',
            '@filesource',
            '@global',
            '@ignore',
            '@internal',
            '@license',
            '@link',
            '@method',
            '@package',
            '@since',
            '@source',
            '@subpackage',
            '@uses',
            '@used-by',
            '@version',
        ];
        $endTags = [
            'T_DOC_COMMENT_CLOSE_TAG',
            'T_DOC_COMMENT_TAG',
        ];

        if (in_array($tokens[$cursor]['content'], $tagsIncorrect)) {
            $msg = $msg . $tokens[$cursor]['content'] . '"';
            $errorStatus = true;
        }
        
        if ($errorStatus) {
            $fix = $phpcsFile->addFixableError($msg, $stackPtr, 'Found');

            if ($fix === true) {
                $cursor++;

                while (!in_array($tokens[$cursor]['type'], $endTags)) {
                    if (!isset($tokens[$cursor]['content'])) {
                        break;
                    }

                    $cursor++;
                }

                if (!isset($tokens[$cursor]['type'])) {
                    return null;
                }

                while ($tokens[$cursor]['type'] != 'T_DOC_COMMENT_STRING') {
                    if (!isset($tokens[$cursor]['content'])) {
                        break;
                    }

                    $cursor--;
                }

                if (!isset($tokens[$cursor]['type'])) {
                    return null;
                }
                
                $cursorEnd = $cursor;
                // find start pos broken block
                $cursor = $stackPtr;

                while ($tokens[$cursor]['content'] == nl2br($tokens[$cursor]['content'])) {
                    if (!isset($tokens[$cursor]['content'])) {
                        break;
                    }

                    $cursor--;
                }

                if (!isset($tokens[$cursor]['type'])) {
                    return null;
                }

                $cursorStr = $cursor;
                // delete broken block
                $phpcsFile->fixer->beginChangeset();
                $cursor = $cursorEnd;

                while ($cursor > $cursorStr - 1) {
                    $phpcsFile->fixer->replaceToken($cursor, '');
                    $cursor--;
                }

                $phpcsFile->fixer->endChangeset();
            }
        }
    }
}

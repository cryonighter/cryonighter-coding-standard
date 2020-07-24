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
 * @api example
 * @author example
 * @category example
 * @copyright example
 * @example example
 * @filesource example
 * @global example
 * @ignore example
 * @internal example
 * @license example
 * @link example
 * @method example
 * @package example
 * @since example
 * @source example
 * @subpackage example
 * @uses example
 * @used-by example
 * @version example
 *
 * @param float $a
 *
 * @return bool
 * 
 * </code>
 */

namespace Cryonighter\Sniffs\Indents;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class StyleIndentsBracketCloseCommentSniff implements Sniff
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
            T_OPEN_CURLY_BRACKET,
            T_OPEN_PARENTHESIS,
            T_OPEN_SQUARE_BRACKET,
            T_OPEN_SHORT_ARRAY,
            T_CLOSE_CURLY_BRACKET,
            T_CLOSE_PARENTHESIS,
            T_CLOSE_SQUARE_BRACKET,
            T_CLOSE_SHORT_ARRAY,
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
        $msg = 'Found COMMENT in bracket line;';
        // token cursor
        $cursor = $stackPtr;
        // fix line
        $fixLine = $tokens[$cursor]['line'];
        $commentTags = [
            'T_COMMENT',
            'T_DOC_COMMENT_OPEN_TAG',
            'T_DOC_COMMENT_STRING',
            'T_DOC_COMMENT_CLOSE_TAG',
        ];
        $targets = [
            'T_OPEN_CURLY_BRACKET',
            'T_OPEN_PARENTHESIS',
            'T_OPEN_SQUARE_BRACKET',
            'T_OPEN_SHORT_ARRAY',
            'T_CLOSE_CURLY_BRACKET',
            'T_CLOSE_PARENTHESIS',
            'T_CLOSE_SQUARE_BRACKET',
            'T_CLOSE_SHORT_ARRAY',
        ];
        $i = 0;

        while (($tokens[$stackPtr]['line'] + 1) >= $tokens[$cursor]['line']) {
            $cursor++;

            if (!isset($tokens[$cursor]['type'])) {
                break;
            }

            if ($tokens[$cursor]['line'] > $fixLine) {
                break;
            }
            
            if (in_array($tokens[$cursor]['type'], $targets)) {
                $i++;
            }

            if (in_array($tokens[$cursor]['type'], $commentTags)) {
                if ($i < 1) {
                    $errorStatus = true;
                }
            }
        }
        
        if ($errorStatus) {
            $phpcsFile->addError($msg, $stackPtr, 'Found');
        }
    }
}

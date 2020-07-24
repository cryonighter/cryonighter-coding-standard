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

namespace Cryonighter\Sniffs\indents;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class StyleIndentsPhpDocCleanerSniff implements Sniff
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
        $msg = 'Incorrect parameter in PhpDoc - "';
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

        if (in_array($tokens[$cursor]['content'], $tagsIncorrect)) {
            $msg = $msg . $tokens[$cursor]['content'] . '"';
            $errorStatus = true;
        }
        
        if ($errorStatus) {
            $phpcsFile->addError($msg, $stackPtr, 'Found');
        }
    }
}

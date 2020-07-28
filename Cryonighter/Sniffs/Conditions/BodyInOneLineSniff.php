<?php

/**
 * This sniff prohibits the use of Perl style hash comments.
 * An example of a hash comment is:
 * <code>
 *   if (
 *       empty($a) ||
 *       empty($b)
 *   ) {
 *       echo 'Hello';
 *   }
 * </code>
 */

namespace Cryonighter\Sniffs\Conditions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class BodyInOneLineSniff implements Sniff
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
            T_IF,
            T_ELSEIF,
            T_DO,
            T_WHILE,
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
        // code...
        return null;
    }
}

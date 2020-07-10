<?php
/**
 * This sniff prohibits the use of Perl style hash comments.
 *
 * An example of a hash comment is:
 *
 * <code>
 * $var = 0 ;
 * </code>
 *
 */

namespace Cryonighter\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class StyleIndentsSemicolonSniff implements Sniff
{

    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = array(
        'PHP',
    );

    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array(int)
     */
    public function register()
    {
        $tokens[] = T_SEMICOLON;

        return $tokens;
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token
     *                                               in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $token  = $tokens[$stackPtr];
        $errorStatus = false;
        $msg = 'Whitespace found before semicolon "%s" symbol;';
        // token cursor
        $cursor = $stackPtr - 1;
        $cursorToken = $tokens[$cursor];

        // check error
        if (in_array($cursorToken['type'], ['T_WHITESPACE', 'T_COMMENT'])) {
            $errorStatus = true;
        }

        // create error
        $data  = array(trim($tokens[$stackPtr]['content']));

        if ($errorStatus) {
            $phpcsFile->addError($msg, $stackPtr, 'Found', $data);
        }
    }
}

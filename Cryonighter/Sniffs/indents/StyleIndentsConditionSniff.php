<?php
/**
 * This sniff prohibits the use of Perl style hash comments.
 *
 * An example of a hash comment is:
 *
 * <code>
 * if ($a = rand(0, 1)) {
 *    return true;
 * }
 * </code>
 */

namespace Cryonighter\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class StyleIndentsConditionSniff implements Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     * @var array
     */
    public $supportedTokenizers = [
        'PHP',
    ];

    /**
     * If TRUE, whitespace rules are not checked for blank lines.
     * Blank lines are those that contain only whitespace.
     * @var boolean
     */
    public $ignoreBlankLines = false;

    /**
     * Returns the token types that this sniff is interested in.
     * @return array
     */
    public function register()
    {
        $tokens[] = T_IF;

        return $tokens;
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
        $msg = 'Whitespace found before semicolon "%s" symbol;';
        // token cursor
        $cursor = $stackPtr;
        
        while ($tokens[$cursor]['type'] != 'T_OPEN_CURLY_BRACKET') {
            $debug[] = var_export($tokens[$cursor], true);
            $cursor++;
        }
        

        // debug
        $debug[] = '';
        $debug = implode("\r\n---------------\r\n", $debug);






        if ($errorStatus) {
            $phpcsFile->addError($debug, $stackPtr, 'Found');
        }
    }
}

<?php
/**
 * This sniff prohibits the use of Perl style hash comments.
 *
 * An example of a hash comment is:
 *
 * <code>
 *   if ($i>1) {
 *       return true;
 *   } else {
 *       return false;
 *   }
 *
 *   return false;
 * </code>
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Dontsov Dmitry ddontsov93@gmail.com
 */

namespace Cryonighter\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class StyleOutputsSniff implements Sniff
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
        $tokens[] = T_RETURN;
        $tokens[] = T_YIELD;
        $tokens[] = T_THROW;

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
        $msg = '';
        // line this token
        $thisLine = $token['line'];
        // position token on prev line
        $stackPtrPrev = $stackPtr - 1;
        // token on prev line
        $tokenPrev  = $tokens[$stackPtrPrev];
        // comment counting
        $i = 0;
        // counting empty lines
        $emptyLines = 0;
        // find prev line
        while (
            $tokenPrev['line'] >= $thisLine ||
            $tokenPrev['type'] == 'T_WHITESPACE' ||
            $tokenPrev['type'] == 'T_COMMENT'
        ) {
            $tokenPrev  = $tokens[$stackPtrPrev];
            if ($tokenPrev['type'] == 'T_COMMENT') {
                $i++;
            }
            $tokenPrev  = $tokens[$stackPtrPrev];
            
            if (
                (
                    $tokenPrev['type'] == 'T_WHITESPACE' ||
                    $tokenPrev['type'] == 'T_COMMENT'
                ) &&
                nl2br($tokenPrev['content']) != $tokenPrev['content']
            ) {
                $emptyLines++;
            }
            
            $stackPtrPrev--;
        }

        // counting empty lines
        $emptyLines = $emptyLines - $i;
        // no space condition error
        $spaceLineSize = $thisLine - $tokenPrev['line'];

        if ($i > 0) {
            $spaceLineSize = $emptyLines;
        }
        $spaceLineCondition = false;

        // an exception - T_OPEN_CURLY_BRACKET
        if ($spaceLineSize < 2 && $tokenPrev['type'] != 'T_OPEN_CURLY_BRACKET') {
            // no empty line translation exception
            $msg = 'Missing empty line found before "%s";';
            $errorStatus = true;
        }

        // Excess empty line translation exception
        if ($spaceLineSize > 1 && $tokenPrev['type'] == 'T_OPEN_CURLY_BRACKET') {
            $msg = 'Excess empty line found before "%s";';
            $errorStatus = true;
        }

        // generate error output
        $data  = array(trim($tokens[$stackPtr]['content']));

        if ($errorStatus) {
            $phpcsFile->addError($msg, $stackPtr, 'Found', $data);
        }
    }
}

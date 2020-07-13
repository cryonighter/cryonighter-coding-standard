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
 */

namespace Cryonighter\Sniffs\Classes;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class StyleOutputsSniff implements Sniff
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
     * @return array | int[]
     */
    public function register()
    {
        $tokens[] = T_RETURN;
        $tokens[] = T_YIELD;
        $tokens[] = T_THROW;

        return $tokens;
    }

    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param File                       $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token in the
     *                                               stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $token  = $tokens[$stackPtr];
        $errorStatus = false;
        $errorType = 0;
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
            in_array($tokenPrev['type'], ['T_WHITESPACE', 'T_COMMENT'])
            || $tokenPrev['line'] >= $thisLine
        ) {
            $tokenPrev = $tokens[$stackPtrPrev];
            if ($tokenPrev['type'] == 'T_COMMENT') {
                $i++;
            }
            $tokenPrev = $tokens[$stackPtrPrev];
            if (
                in_array($tokenPrev['type'], ['T_WHITESPACE', 'T_COMMENT'])
                && nl2br($tokenPrev['content']) != $tokenPrev['content']
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
        // an exception - T_OPEN_CURLY_BRACKET
        if ($spaceLineSize < 2 && $tokenPrev['type'] != 'T_OPEN_CURLY_BRACKET') {
            // no empty line translation exception
            $msg = 'Missing empty line found before "%s";';
            $errorType = 1;
            $errorStatus = true;
        }
        // Excess empty line translation exception
        if ($spaceLineSize > 1 && $tokenPrev['type'] == 'T_OPEN_CURLY_BRACKET') {
            $msg = 'Excess empty line found before "%s";';
            $errorType = -1;
            $errorStatus = true;
        }
        // generate error output
        $data[] = trim($tokens[$stackPtr]['content']);
        if ($errorStatus) {
            $fix = $phpcsFile->addFixableError($msg, $stackPtr, 'Found', $data);
            // fix problems
            if ($fix === true) {
                if ($errorType > 0) {
                    $phpcsFile->fixer->addNewlineBefore($stackPtr);
                } else {
                    $phpcsFile->fixer->beginChangeset();
                    $target = $thisLine - $spaceLineSize;
                    $cursor = $stackPtr - 1;
                    while ($tokens[$cursor]['line'] > $target) {
                        $phpcsFile->fixer->replaceToken($cursor, '');
                        $cursor--;
                    }
                    $phpcsFile->fixer->endChangeset();
                }
            }
        }
    }
}

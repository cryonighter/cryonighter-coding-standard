<?php

/**
 * This sniff find and fix empty line return before
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
 */

namespace Cryonighter\Sniffs\LineBreaks;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class ReturnEmptyLineBeforeSniff implements Sniff
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
            T_RETURN,
            T_YIELD,
            T_THROW,
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
        $token = $tokens[$stackPtr];
        $errorStatus = false;
        $errorType = 0;
        $msg = '';
        // line this token
        $thisLine = $token['line'];
        // position token on prev line
        $stackPtrPrev = $stackPtr - 1;
        // token on prev line
        $tokenPrev = $tokens[$stackPtrPrev];
        // comment counting
        $i = 0;
        // comment before return
        $cursor = $stackPtr;
        // counting empty lines
        $emptyLines = 0;

        // find prev line
        while (in_array($tokenPrev['type'], ['T_WHITESPACE', 'T_COMMENT']) || $tokenPrev['line'] >= $thisLine) {
            $tokenPrev = $tokens[$stackPtrPrev];

            if ($tokenPrev['type'] == 'T_COMMENT') {
                $i++;
                $cursor = $stackPtrPrev;
            }

            $tokenPrev = $tokens[$stackPtrPrev];

            if (in_array($tokenPrev['type'], ['T_WHITESPACE', 'T_COMMENT']) && nl2br($tokenPrev['content']) != $tokenPrev['content']) {
                $emptyLines++;
            }

            $stackPtrPrev--;
        }
        
        if ($tokens[$cursor - 1]['content'] === nl2br($tokens[$cursor - 1]['content'])) {
            $cursor--;
        }

        // counting empty lines
        $emptyLines = $emptyLines - $i;
        // no space condition error
        $spaceLineSize = $thisLine - $tokenPrev['line'];

        if ($i > 0) {
            $spaceLineSize = $emptyLines;
        }

        if ($spaceLineSize < 2 && $tokenPrev['type'] != 'T_OPEN_CURLY_BRACKET') {
            // no empty line translation exception
            $msg = 'LineBreaks. Missing empty line found before "%s";';
            $errorType = 1;
            $errorStatus = true;
        }

        // Excess empty line translation exception
        if ($spaceLineSize > 1 && $tokenPrev['type'] == 'T_OPEN_CURLY_BRACKET') {
            $msg = 'LineBreaks. Excess empty line found before "%s";';
            $errorType = -1;
            $errorStatus = true;
        }

        // generate error output
        $data = [];
        $data[] = trim($tokens[$stackPtr]['content']);

        if ($errorStatus) {
            $fix = $phpcsFile->addFixableError($msg, $stackPtr, 'Found', $data);

            if ($fix === true) {
                if ($errorType > 0) {
                    $content = "\r\n" . $tokens[$cursor]['content'];
                    $phpcsFile->fixer->beginChangeset();
                    $phpcsFile->fixer->replaceToken($cursor, $content);
                    $phpcsFile->fixer->endChangeset();
                    // $phpcsFile->fixer->addNewlineBefore($cursor);
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

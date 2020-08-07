<?php

/**
 * This sniff check calling undefined arrays
 *
 * An example:
 *
 * <code>
 *   $arr[] = 7;
 * </code>
 */

namespace Cryonighter\Sniffs\Arrays;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class CallUndefinedSniff implements Sniff
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
            T_OPEN_SQUARE_BRACKET,
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
        $msg = 'Arrays. Call to undefined array - ';
        $cursor = $stackPtr;
        $arrayToken = $cursor;
        $arrayVariable = 'array';
        $cursor--;

        // find variable
        while ($tokens[$cursor]['type'] == 'T_WHITESPACE') {
            if (!isset($tokens[$cursor]['content'])) {
                break;
            }

            $cursor--;
        }

        if (!isset($tokens[$cursor]['content'])) {
            return null;
        }

        $arrayToken = $cursor;
        $arrayVariable = $tokens[$arrayToken]['content'];
        $definition = false;
        $cursor--;
        $subCursor = $cursor;
        
        //find definition variable
        while ($tokens[$cursor]['content'] != $arrayVariable && $definition != true) {
            if (!isset($tokens[$cursor]['content']) || $cursor < 2) {
                $errorStatus = true;
                break;
            }

            // check definition
            if ($tokens[$cursor]['content'] == $arrayVariable) {
                $subCursor = $cursor;
                
                while ($tokens[$subCursor]['type'] != 'T_OPEN_SHORT_ARRAY' || $tokens[$subCursor]['type'] != 'T_ARRAY') {
                    if (!isset($tokens[$subCursor]['content']) || $subCursor >= $arrayToken) {
                        $errorStatus = true;
                        break;
                    }

                    $subCursor++;
                }
                
                $definition = true;
            }

            $cursor--;
        }

        $msg .= '"' . $arrayVariable . '";';

        if ($errorStatus) {
            $phpcsFile->addError($msg, $stackPtr, 'Found');
        }
    }
}

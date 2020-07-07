<?php
/**
 * This sniff prohibits the use of Perl style hash comments.
 *
 * An example of a hash comment is:
 *
 * <code>
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
        $tokens = Tokens::$functionNameTokens;

        $tokens[] = T_RETURN;
        $tokens[] = T_YIELD;
        $tokens[] = T_THROW;

        return $tokens;

    }//end register()


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
        
        // $cnt = 1;
        
      
        // if ($cnt == 1) {
        //     $error = 'error testing); found %s';
        //     $data  = array(trim($tokens[$stackPtr]['content']));
        //     $phpcsFile->addError($error, $stackPtr, 'Found', $data);
        //     return;
        //     exit();
        // }  

    }//end process()
}//end class
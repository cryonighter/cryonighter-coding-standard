<?php

/**
 * This sniff checking and fixed php-doc params order
 * P.S. If they sleepin' on this script, the hoes better get insomnia
 * An example of a hash comment is:
 *
 * <code>
 *  * @ throws BadRequestHttpException
 *  * @ throws AccessDeniedHttpException
 *  *
 *  * @ param PaymentRequest $paymentRequest
 *  * @ param Request        $request
 *  *
 *  * @ return Response
 *  *
 *  * @ Route("/payment")
 * </code>
 */

namespace Cryonighter\Sniffs\Comments;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class OrderOfPhpDocSniff implements Sniff
{
    /**
    * The file being scanned.
    *
    * @var File|null
    */
    private $phpcsFile = null;

    /**
     * The position of the current token in the stack passed in $tokens.
     * @var int
     */
    private $stackPtr = 0;

    /**
    * Array of tokens found in the scanned file
    *
    * @var array|null
    */
    private $tokens = null;

    /**
     * Error correction algorithm type
     * 1 - change order main tags (default)
     * 2 - lifting up other tags
     *
     * @var int
     */
    private $errType = 1;

    /**
     * measuring pints is blood
     * on the Louis V carpet
     *
     * @var int
     */
    private $counters = [];

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
            T_DOC_COMMENT_OPEN_TAG,
        ];
    }

    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        // we all go for amendments to the constitution
        $this->counters = [];
        $this->phpcsFile = $phpcsFile;
        $this->tokens = $this->phpcsFile->getTokens();
        $this->stackPtr = $stackPtr;
        $this->mainLoop();
    }

    /**
     * Encapsulation from external recursions
     *
     * @return void
     */
    private function mainLoop()
    {
        $cursor = $this->stackPtr;
        $commentTags = [];
        // Tokens used for parsing doc blocks.
        $phpDocTokens = [
            'T_DOC_COMMENT_STAR',
            'T_DOC_COMMENT_WHITESPACE',
            'T_DOC_COMMENT_TAG',
            'T_DOC_COMMENT_OPEN_TAG',
            'T_DOC_COMMENT_CLOSE_TAG',
            'T_DOC_COMMENT_STRING',
        ];
        $targetTags = [
            '@param',
            '@return',
            '@throws',
            '@var',
        ];

        // find all tags
        while ($this->tokens[$cursor]['type'] != 'T_DOC_COMMENT_CLOSE_TAG') {
            // foolproof
            if (!isset($this->tokens[$cursor]['content'])) {
                return null;
            }

            // break php doc
            if (!in_array($this->tokens[$cursor]['type'], $phpDocTokens)) {
                return null;
            }

            // saving up currect tags
            if (in_array($this->tokens[$cursor]['content'], $targetTags)) {
                $commentTags[$cursor] = $this->tokens[$cursor]['content'];
            } elseif ($this->tokens[$cursor]['type'] == 'T_DOC_COMMENT_TAG' && !empty($commentTags)) {
                $msg = 'Comments. This tag must be located above.';
                // set the correct fixer
                $this->errType = 2;
                // create error
                $this->generateError($cursor, $msg, $commentTags);
            }

            $cursor++;
        }

        // skip docs without tags
        if (empty($commentTags)) {
            return null;
        }

        // check tags sort order
        $currentOrderArray = $commentTags;
        asort($currentOrderArray);

        // sorting ok
        if ($currentOrderArray === $commentTags) {
            return null;
        }

        // generate error output
        $this->generateError($cursor, '', $commentTags);
    }

    /**
     * find start and end block coordinates
     *
     * @param int       $cursor
     * @param string    $closeBlockCondition A dot denoting the end of a block as a token content
     * @param string    $closeBlockPreCondition A dot at which, having found which we expect the tag content to be closed
     * @param bool|null $afterLines Check lines before tag
     *
     * @return array|null two elements start/end adress and content all block
     */
    private function findAllBlock($cursor, $closeBlockCondition = '', $closeBlockPreCondition = '', $afterLines = null)
    {
        // any recursion needs a condom and lubricant
        if (!isset($this->counters[__FUNCTION__])) {
            $this->counters[__FUNCTION__] = 0;
        }

        $this->counters[__FUNCTION__]++;
        
        // check literation
        if (!isset($this->tokens[$cursor]['content'])) {
            return null;
        }

        // remote controll
        if ($this->counters[__FUNCTION__] > 500) {
            $this->counters[__FUNCTION__] = 0;
            $msg = 'Cryonighter. data decomposition error. Do you like spaghetti?';
            $this->generateError(1, $msg);
            
            return null;
        }

        // base array structure
        $result = [
            'start' => 0,
            'end' => 0,
            'content' => '',
        ];
        // finding start
        $defaultStack = $cursor;

        // while to column 1
        while ($this->tokens[$cursor]['column'] > 1) {
            // foolproof
            if (!isset($this->tokens[$cursor]['content'])) {
                return null;
            }

            $cursor--;
        }

        $result['start'] = $cursor;
        $cursor = $defaultStack;

        while ($this->tokens[$cursor]['content'] == nl2br($this->tokens[$cursor]['content'])) {
            // foolproof
            if (!isset($this->tokens[$cursor]['content'])) {
                return null;
            }

            // Looking for a precondition on the current line and checking the need to look for a closing token
            if (stripos($this->tokens[$cursor]['content'], $closeBlockPreCondition) !== false) {
                // Looking for a closing tag
                while (stripos($this->tokens[$cursor]['content'], $closeBlockCondition) === false) {
                    // foolproof
                    if (!isset($this->tokens[$cursor]['content'])) {
                        return null;
                    }
                    
                    $cursor++;
                }

                $cursor++;
                $result['end'] = $cursor;
                // found a closed tag - throw cycle
                break;
            }

            // found a closed tag - throw cycle
            if (!empty($result['end'])) {
                break;
            }

            $cursor++;
        }

        // The FIRST token CANNOT be the LAST globally when it is part of a BLOCK
        if (!isset($this->tokens[$cursor]['content'])) {
            return null;
        }

        // If no closing tag is found, then we read the last tag in the current line
        if (empty($result['end'])) {
            $result['end'] = $cursor;
        }

        // added empty sapaces
        while ($this->tokens[$cursor]['type'] == 'T_DOC_COMMENT_WHITESPACE') {
            if (!isset($this->tokens[$cursor]['content'])) {
                break;
            }

            $cursor++;
        }

        // Ignore last literation
        $cursor--;
        // Collecting the contents of the block
        $result['content'] = [];

        for ($stack = $result['start']; $stack <= $result['end']; $stack++) {
            $result['content'][] = $this->tokens[$stack]['content'];
        }

        $result['content'] = implode('', $result['content']);
        
        if ($afterLines !== null) {
            // check the lines after the tag
            $this->checkAndMergeLine($result, true);
            
            // check the lines before the tag
            if ($afterLines) {
                $this->checkAndMergeLine($result, false);
            }

            // Collecting the contents of the block
            $result['content'] = [];

            for ($stack = $result['start']; $stack <= $result['end']; $stack++) {
                $result['content'][] = $this->tokens[$stack]['content'];
            }

            $result['content'] = implode('', $result['content']);
        }
            
        // get out
        return $result;
    }

    /**
     * Checking and combining TWO lines
     * it turns out php seven - fully shit
     * proof function.
     *
     * @param array   $result findAllBlock arr type
     * @param boolean $after content merge type
     * @param string  $safeword this is one word
     * @param int     $maxCalling maximum amount of literature
     *
     * @return findAllBlock arr type
     */
    private function checkAndMergeLine(&$result, $after = true, $safeword = '*', $maxCalling = 10)
    {
        // check calling
        if (!isset($result['end']) && !isset($result['start'])) {
            return null;
        }

        // enable many execution
        $prefix = '';

        // this is before or after line?
        if ($after !== true) {
            $prefix = 'before';
            $cursor = $result['start'] - 1;
        } else {
            $cursor = $result['end'] + 1;
            $prefix = 'after';
        }

        // create new literation couner
        if (!isset($this->counters[__FUNCTION__][$prefix])) {
            $this->counters[__FUNCTION__][$prefix] = 0;
        }

        // remote control
        if ($this->counters[__FUNCTION__][$prefix] >= $maxCalling) {
            return null;
        }
        
        // find active recursion
        foreach ($this->counters[__FUNCTION__] as $key => $item) {
            // once - not ****
            if ($key == $prefix) {
                continue;
            }

            // another recursion in progress
            if ($item !== 0) {
                return null;
            }
        }

        $this->counters[__FUNCTION__][$prefix]++;

        // pre check
        if ($this->counters[__FUNCTION__][$prefix] > 1 && trim($result['content']) != $safeword) {
            return null;
        }

        // one second... shoot in the head or more recursion?
        $extraLine = $this->findAllBlock($cursor, '', '', !$after);
            
        // Colt 45? I'ma need somethin' stronger
        if (!isset($extraLine['content'])) {
            return null;
        }

        // post check
        if ($this->counters[__FUNCTION__][$prefix] > 1 && trim($extraLine['content']) != $safeword) {
            // constitutional amendments
            $this->counters[__FUNCTION__][$prefix] = 0;
            
            return null;
        }

        // Merge lines
        if ($after !== true) {
            $result['start'] = $extraLine['start'];
            $result['content'] = $extraLine['content'] . $result['content'];
        } else {
            $result['end'] = $extraLine['end'];
            $result['content'] = $result['content'] . $extraLine['content'];
        }

        return $result;
    }

    /**
     * generate error output
     *
     * @param string $msg
     * @param int    $cursor
     * @param array  $commentTags - array with key tags
     *
     * @return null
     */
    private function generateError($cursor, $msg = '', $commentTags = [])
    {
        // default error
        if (empty($msg)) {
            // Warring!!! Very Worst scenario for fixer
            $this->errType = 1;
            $msg = 'Comments. Disorder in annotation block.';
        }

        // no auto-fixableble error
        if (empty($commentTags)) {
            $this->phpcsFile->addError($msg, $cursor, 'Found');
            
            return null;
        }

        $fix = $this->phpcsFile->addFixableError($msg, $cursor, 'Found');
        $this->fixThisError($fix, $cursor, $commentTags);
    }

    /**
     * We try to automatically resolve errors
     * Attention! One literation - One error - One solution. NO MORE!
     *
     * @param boolean $fix Variable describing the status of the error solution
     * @param array   $commentTags - array with key tags
     * @param int     $cursor
     *
     * @return null    Early exit from the procedure
     */
    private function fixThisError($fix, $cursor, $commentTags)
    {
        // Skip resolved problem
        if (!$fix) {
            return null;
        }

        $this->phpcsFile->addError('fix error', $cursor, 'Found');

        // error type 2 - lifting up other tags
        if ($this->errType == 2) {
            // swap another tag and the first element of the array with the main tags
            $otherTag = $this->findAllBlock($cursor, ')', '(', true);
            $mainTagStag = array_keys($commentTags);
            $mainTagStag = $mainTagStag[0];
            $mainTag = $this->findAllBlock($mainTagStag, ')', '(');
            $this->phpcsFile->fixer->beginChangeset();
            
            // clean line outher tag
            for ($cursor = $otherTag['end']; $cursor >= $otherTag['start']; $cursor--) {
                // foolproof
                if (!isset($this->tokens[$cursor]['content'])) {
                    break;
                }

                // delete block tokens part
                $this->phpcsFile->fixer->replaceToken($cursor, '');
            }
            
            // raising an inappropriate tag
            $this->phpcsFile->fixer->replaceToken($mainTag['start'] - 1, "\r\n" . $otherTag['content']);
            $this->phpcsFile->fixer->endChangeset();
        }

        // error type 1 - change order main tags
        if ($this->errType == 1) {
            // no suicide note, just a note for target distance...
            $correctOrder = $commentTags;
            asort($correctOrder);
            // declare an array
            $substituteTag = [];
            $this->phpcsFile->fixer->beginChangeset();

            // iterate over the main array introducing a new order
            foreach ($commentTags as $key => $commentTag) {
                // find the item to replace
                $commentTag = $this->findAllBlock($key, ')', '(');
                // clean incorrect line
                for ($cursor = $commentTag['end']; $cursor >= $commentTag['start']; $cursor--) {
                    // foolproof
                    if (!isset($this->tokens[$cursor]['content'])) {
                        break;
                    }

                    // delte token in block
                    $this->phpcsFile->fixer->replaceToken($cursor, '');
                }

                // preparing a tag for replacement by shifting the internal array pointer
                $substituteTag = each($correctOrder);
                $substituteTagStag = $substituteTag['key'];
                $substituteTag = $this->findAllBlock($substituteTagStag, ')', '(');
                $this->phpcsFile->fixer->replaceToken($commentTag['start'], $substituteTag['content']);
            }

            $this->phpcsFile->fixer->endChangeset();
            // gentleman's rule
            reset($correctOrder);
        }
    }
}

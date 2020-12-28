<?php

namespace Cryonighter\Tests\Comments;

/**
 * This file errors example
 * @param int  $stackPtr  The position of the current token
 * @param int  $stackPtr The position of the current token
 * @param File $phpcsFile The file being scanned.
 * @param File $phpcsFile The file being scanned.
 * @param int  $stackPtr The position of the current token
 */

class Test
{
    /**
     * @Route("/payment")
     *
     * @param Request        $request
     * @param PaymentRequest $paymentRequest
     * @return Request
     */
    public function testFunction(PaymentRequest $paymentRequest, Request $request)
    {
        return $request;
    }
}

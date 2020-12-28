<?php

namespace Cryonighter\Tests\Comments;

/**
 * This file errors example
 */

class Test
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type("string")
     * @Assert\Length(min = 4, max = 4)
     * @Assert\Length(min = 4, max = 4)
     * @var string
     * @Assert\Regex(
     *     pattern="/\d/",
     *     message="Must contain a number"
     * )
     *
     */
    public $expYear;

    /**
     *
     * @return Response
     * @param PaymentRequest $paymentRequest
     * @throws BadRequestHttpException
     *
     *
     * @Route("/payment")
     *
     *
     * @throws AccessDeniedHttpException
     * @param Request        $request
     */
    public function testFunction(PaymentRequest $paymentRequest, Request $request)
    {
        return $request;
    }
}

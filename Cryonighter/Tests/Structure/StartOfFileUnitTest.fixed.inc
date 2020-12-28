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
     * @Assert\Regex(
     *     pattern="/\d/",
     *     message="Must contain a number"
     * )
     *
     * @var string
     */
    public $expYear;

    /**
     *
     *
     *
     * @Route("/payment")
     *
     *
     * @param PaymentRequest $paymentRequest
     * @param Request        $request
     * @return Response
     * @throws BadRequestHttpException
     * @throws AccessDeniedHttpException
     */
    public function testFunction(PaymentRequest $paymentRequest, Request $request)
    {
        return $request;
    }
}

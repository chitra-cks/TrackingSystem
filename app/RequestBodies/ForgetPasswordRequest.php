<?php

namespace App\requestBodies;

/**
 * @OA\Schema(
 *      title="User ForgetPassword request body",
 *      description="User ForgetPassword all request variable",
 *      type="object",
 * )
 */
class ForgetPasswordRequest
{
    /**
     *	@OA\Property(
     *		title="email",
     *		description="email of user"
     *	)
     *
     * @var string
     */
    protected $email;

}
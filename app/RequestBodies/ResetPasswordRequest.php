<?php

namespace App\requestBodies;

/**
 * @OA\Schema(
 *      title="User ResetPassword request body",
 *      description="User ResetPassword all request variable",
 *      type="object",
 * )
 */
class ResetPasswordRequest
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

    /**
     *  @OA\Property(
     *      title="password",
     *      description="New password of user"
     *  )
     *
     * @var string
     */
    protected $password;

    /**
     *  @OA\Property(
     *      title="confirm_password",
     *      description="confirm password of user"
     *  )
     *
     * @var string
     */
    protected $confirm_password;

    /**
     *  @OA\Property(
     *      title="token",
     *      description="reset password token of user"
     *  )
     *
     * @var string
     */
    protected $token;
}
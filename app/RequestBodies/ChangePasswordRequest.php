<?php

namespace App\requestBodies;

/**
 * @OA\Schema(
 *      title="User ChangePassword request body",
 *      description="User ChangePassword all request variable",
 *      type="object",
 * )
 */
class ChangePasswordRequest
{
    /**
     *	@OA\Property(
     *		title="current_password",
     *		description="Current password of user"
     *	)
     *
     * @var string
     */
    protected $current_password;

    /**
     *  @OA\Property(
     *      title="new_password",
     *      description="New password of user"
     *  )
     *
     * @var string
     */
    protected $new_password;

    /**
     *  @OA\Property(
     *      title="confirm_password",
     *      description="confirm password of user"
     *  )
     *
     * @var string
     */
    protected $confirm_password;
}

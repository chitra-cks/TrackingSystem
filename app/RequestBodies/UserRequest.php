<?php

namespace App\requestBodies;

/**
 * @OA\Schema(
 *      title="User request body",
 *      description="User all request variable",
 *      type="object",
 * )
 */
class UserRequest
{
    /**
      *	@OA\Property(
      *		title="firstname",
      *		description="firstname of user"
      *	)
      *
      * @var string
      */
    protected $firstname;

    /**
      *	@OA\Property(
      *		title="lastname",
      *		description="lastname of user"
      *	)
      *
      * @var string
      */
    protected $lastname;

    /**
     *	@OA\Property(
     *		title="user_type",
     *		description="Role of the user"
     *	)
     *
     * @var integer
     */

    protected $user_type;
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
     *	@OA\Property(
     *		title="password",
     *		description="password of user"
     *	)
     *
     * @var string
     */

    protected $password;

    /**
     *	@OA\Property(
     *		title="mobile",
     *		description="Mobile number"
     *	)
     *
     * @var string
     */

    protected $mobile;

    /**
     *  @OA\Property(
     *      title="address",
     *      description="Address of user"
     *  )
     *
     * @var text
     */

    protected $address;

    /**
     *  @OA\Property(
     *      title="gender",
     *      description="Gender of user"
     *  )
     *
     * @var string
     */

    protected $gender;

    /**
     *  @OA\Property(
     *      title="status",
     *      description="status of user"
     *  )
     *
     * @var string
     */

    protected $status;

    /**
    *  @OA\Property(
    *    title="_method",
    *    description="Use only when put method and value must be put"
    *  )
    *
    * @var string
    */
    protected $_method;
}

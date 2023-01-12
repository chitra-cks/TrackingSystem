<?php

namespace App\requestBodies;

/**
 * @OA\Schema(
 *      title="Role request body",
 *      description="Role all request variable",
 *      type="object",
 * )
 */
class RoleRequest
{
    /**
      *	@OA\Property(
      *		title="name",
      *		description="Name of Role"
      *	)
      *
      * @var string
      */
    protected $name;

    /**
      *	@OA\Property(
      *		title="role_id",
      *		description="ID of Role"
      *	)
      *
      * @var string
      */
    protected $role_id;

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

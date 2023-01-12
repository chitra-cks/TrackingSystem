<?php

namespace App\requestBodies;

/**
 * @OA\Schema(
 *      title="Vendors request body",
 *      description="Vendors all request variable",
 *      type="object",
 * )
 */
class VendorRequest
{
    /**
      *	@OA\Property(
      *		title="firstname",
      *		description="Firstname of vendor"
      *	)
      *
      * @var string
      */
    protected $firstname;

    /**
      *  @OA\Property(
      *      title="lastname",
      *      description="Lastname of vendor"
      *  )
      *
      * @var string
      */
    protected $lastname;

    /**
    *  @OA\Property(
    *      title="mobile",
    *      description="mobile number of vendor"
    *  )
    *
    * @var string
    */
    protected $mobile;

    /**
    *  @OA\Property(
    *      title="price",
    *      description="Price  per piece of vendors"
    *  )
    *
    * @var double
    */
    protected $price;

    /**
    *  @OA\Property(
    *      title="status",
    *      description="Vendor status enable or disable
"
    *  )
    *
    * @var string
    */
    protected $status;

    /**
     *  @OA\Property(
     *    title="_method",
     *    description="Use only when put method and value must be 'put'"
     *  )
     *
     * @var string
     */
    protected $_method;
}

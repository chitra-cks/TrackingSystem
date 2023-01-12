<?php

namespace App\requestBodies;

/**
 * @OA\Schema(
 *      title="Raw material request body",
 *      description="raw material all request variable",
 *      type="object",
 * )
 */
class RawMaterialRequest
{
    /**
      *	@OA\Property(
      *		title="quantity",
      *		description="Design name or title
"
      *	)
      *
      * @var string
      */
    protected $quantity;

    /**
      *	@OA\Property(
      *		title="source",
      *		description="Design Number
"
      *	)
      *
      * @var string
      */
    protected $source;

    /**
    *  @OA\Property(
    *      title="price",
    *      description="Price per piece"
    *  )
    *
    * @var double
    */
    protected $price;

    /**
    *  @OA\Property(
    *      title="voucher_number",
    *      description="voucher_number of  sarees"
    *  )
    *
    * @var string
    */
    protected $voucher_number;

    /**
    *  @OA\Property(
    *      title="voucher",
    *      description="Voucher image/PDF name
"
    *  )
    *
    * @var string
    */
    protected $voucher;

    /**
     *  @OA\Property(
     *      title="voucher_mimetype",
     *      description="Mime type to identify the type"
     *  )
     *
     * @var string
     */
    protected $voucher_mimetype;


    /**
    *  @OA\Property(
    *      title="LR_number",
    *      description="LR_number  of  sarees"
    *  )
    *
    * @var string
    */
    protected $LR_number;


    /**
     *  @OA\Property(
     *      title="LR",
     *      description="LR image/PDF name"
     *  )
     *
     * @var string
     */
    protected $LR;

    /**
     *  @OA\Property(
     *      title="LR_mimetype",
     *      description="Mime type to identify the type"
     *  )
     *
     * @var string
     */
    protected $LR_mimetype;

    /**
     *  @OA\Property(
     *      title="status",
     *      description="Design status enable or disable"
     *  )
     *
     * @var string
     */
    protected $status;

    /**
     *  @OA\Property(
     *      title="pickup_date",
     *      description="Pickup date of  sarees"
     *  )
     *
     * @var date
     */
    protected $pickup_date;

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

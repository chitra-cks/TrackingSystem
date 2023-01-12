<?php

namespace App\requestBodies;

/**
 * @OA\Schema(
 *      title="Job History request body",
 *      description="Job history all request variable",
 *      type="object",
 * )
 */
class JobHistoryRequest
{
    /**
      * @OA\Property(
      *   title="source_vendor_id",
      *   description="source of vendor"
      * )
      *
      * @var string
      */
    protected $source_vendor_id;
    /**
      * @OA\Property(
      *   title="assign_vendor_id",
      *   description="assign of vendor"
      * )
      *
      * @var string
      */
    protected $assign_vendor_id;
    /**
      * @OA\Property(
      *   title="source_place",
      *   description="Source Place"
      * )
      *
      * @var string
      */
    protected $source_place;
    /**
      * @OA\Property(
      *   title="job_id",
      *   description="id of job"
      * )
      *
      * @var string
      */
    protected $job_id;
    /**
      * @OA\Property(
      *   title="design_id",
      *   description="id of design"
      * )
      *
      * @var string
      */
    protected $design_id;
    /**
      *	@OA\Property(
      *		title="quantity",
      *		description="Quantity of sarees"
      *	)
      *
      * @var integer
      */
    protected $quantity;

    /**
      *	@OA\Property(
      *		title="voucher_number",
      *		description="voucher_number of  sarees"
      *	)
      *
      * @var string
      */
    protected $voucher_number;

    /**
    *  @OA\Property(
    *      title="voucher_bill",
    *      description="voucher_bill of  sarees"
    *  )
    *
    * @var string
    */
    protected $voucher_bill;

    /**
    *  @OA\Property(
    *      title="voucher_bill_mimetype",
    *      description="voucher_bill_mimetype of  sarees"
    *  )
    *
    * @var string
    */
    protected $voucher_bill_mimetype;

    /**
    *  @OA\Property(
    *      title="status",
    *      description="job history status inprocess, return, cancel, completed of  sarees"
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
    *      title="job_start_date",
    *      description="job start date  of  sarees"
    *  )
    *
    * @var date
    */
    protected $job_start_date;


    /**
     *  @OA\Property(
     *      title="job_end_date",
     *      description="job end date  of  sarees"
     *  )
     *
     * @var date
     */
    protected $job_end_date;

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

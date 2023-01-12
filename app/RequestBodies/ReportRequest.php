<?php

namespace App\requestBodies;

/**
 * @OA\Schema(
 *      title="Job History request body",
 *      description="Job history all request variable",
 *      type="object",
 * )
 */
class ReportRequest
{
    /**
      *	@OA\Property(
      *		title="vendor",
      *		description="id of vendor"
      *	)
      *
      * @var string
      */
    protected $vendor;

    /**
      *	@OA\Property(
      *		title="vendor_type",
      *		description="type of  vendor"
      *	)
      *
      * @var string
      */
    protected $vendor_type;

    /**
    *  @OA\Property(
    *      title="job_phase",
    *      description="type of job"
    *  )
    *
    * @var string
    */
    protected $job_phase;

    /**
    *  @OA\Property(
    *      title="design",
    *      description="title of  design"
    *  )
    *
    * @var string
    */
    protected $design;

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
     *      title="voucher_number",
     *      description="voucher_number  of  voucher"
     *  )
     *
     * @var string
     */
    protected $voucher_number;


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

  
}
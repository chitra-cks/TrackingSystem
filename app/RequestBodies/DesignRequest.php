<?php

namespace App\requestBodies;

/**
 * @OA\Schema(
 *      title="Design Name request body",
 *      description="Design  name or title ,design number and status all request variable",
 *      type="object",
 * )
 */
class DesignRequest
{
    /**
      *	@OA\Property(
      *		title="title",
      *		description="Design name or title"
      *	)
      *
      * @var string
      */
    protected $title;

    /**
     *  @OA\Property(
     *      title="design_no",
     *      description="Design number"
     *  )
     *
     * @var string
     */
    protected $design_no;

    /**
      *	@OA\Property(
      *		title="status",
      *		description="Job status enable or disable"
      *	)
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

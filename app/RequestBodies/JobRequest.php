<?php

namespace App\requestBodies;

/**
 * @OA\Schema(
 *      title="Job Name request body",
 *      description="Job phase name or title and status all request variable",
 *      type="object",
 * )
 */
class JobRequest
{
    /**
      *	@OA\Property(
      *		title="title",
      *		description="Job phase name or title"
      *	)
      *
      * @var string
      */
    protected $title;

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

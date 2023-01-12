<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use ArrayObject;
use Config;
use App\Traits\RestApi;
use App\Models\Job_history;
use Illuminate\Support\Carbon;
use Auth;

class ReportController extends Controller
{
    use RestApi;
    protected $jobhistories;
    public function __construct(Job_history $jobhistories)
    {
        $this->emptyArrayObject = new ArrayObject();
        $this->jobhistories = $jobhistories;
    }

    /**
    * @OA\Post(
    *       path="/api/reports",
    *       tags={"Reports"},
    *       summary="Display report of listings",
    *       @OA\RequestBody(
    *           required=false,
    *           @OA\JsonContent(ref="#/components/schemas/ReportRequest")
    *       ),
    *       @OA\Response(
    *           response="200", description="Report listings  are fetch successfully"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */
    public function index(Request $request)
    {
        try {
            $requestdata = $this->datasearch($request->all());
            $reports = Job_history::with('Job', 'Assignvendor', 'Sourcevendor')
                            ->where(function ($query) use ($requestdata) {
                                if (isset($requestdata['source_vendor']) && $requestdata['source_vendor'] != null) {
                                    return  $query->where('source_vendor_id', $requestdata['source_vendor']);
                                }
                            })
                            ->where(function ($query) use ($requestdata) {
                                if (isset($requestdata['source_place']) && $requestdata['source_place'] != null) {
                                    return  $query->where('source_place', 'ilike', '%' . $requestdata['source_place'] . '%');
                                }
                            })
                            ->where(function ($query) use ($requestdata) {
                                if (isset($requestdata['assign_vendor']) && $requestdata['assign_vendor'] != null) {
                                    return  $query->where('assign_vendor_id', $requestdata['assign_vendor']);
                                }
                            })
                            ->where(function ($query) use ($requestdata) {
                                if (isset($requestdata['quantity']) && $requestdata['quantity'] != null) {
                                    return $query->where('quantity', 'ilike', '%' . $requestdata['quantity'] . '%');
                                }
                            })
                            ->where(function ($query) use ($requestdata) {
                                if (isset($requestdata['job']) && $requestdata['job'] !=null) {
                                    return $query->where('job_id', $requestdata['job']);
                                }
                            })
                            ->where(function ($query) use ($requestdata) {
                                if (isset($requestdata['design']) && $requestdata['design'] !=null) {
                                    return $query->where('design_id', $requestdata['design']);
                                }
                            })
                            ->where(function ($query) use ($requestdata) {
                                if (isset($requestdata['voucher_number']) && $requestdata['voucher_number'] !=  null) {
                                    return $query->where('voucher_number', 'ilike', '%' . $requestdata['voucher_number'] . '%');
                                }
                            })
                            ->where(function ($query) use ($requestdata) {
                                if (isset($requestdata['pickupdate_start']) && $requestdata['pickupdate_start'] != null && !isset($requestdata['pickupdate_end']) && $requestdata['pickupdate_end'] == null) {
                                    return $query->whereDate('pickup_date', Carbon::parse($requestdata['packupdate_start'])->format('Y-m-d'));
                                } elseif (isset($requestdata['pickupdate_end']) && $requestdata['pickupdate_end'] != null && !isset($requestdata['pickupdate_start']) && $requestdata['pickupdate_start'] == null) {
                                    return $query->whereDate('pickup_date', Carbon::parse($requestdata['packupdate_end'])->format('Y-m-d'));
                                } elseif (isset($requestdata['pickupdate_start']) && $requestdata['pickupdate_start'] != null && !isset($requestdata['pickupdate_end']) && $requestdata['pickupdate_end'] == null) {
                                    return $query->whereDate('pickup_date', '>=', Carbon::parse($requestdata['pickupdate_start'])->format('Y-m-d'))->whereDate('pickup_date', '<=', Carbon::parse($requestdata['pickupdate_end'])->format('Y-m-d'));
                                }
                            })
                            ->where(function ($query) use ($requestdata) {
                                if (isset($requestdata['startdate_start']) && $requestdata['startdate_start'] != null && !isset($requestdata['startdate_end']) && $requestdata['startdate_end'] == null) {
                                    return $query->whereDate('job_start_date', Carbon::parse($requestdata['packupdate_start'])->format('Y-m-d'));
                                } elseif (isset($requestdata['startdate_end']) && $requestdata['startdate_end'] != null && !isset($requestdata['startdate_start']) && $requestdata['startdate_start'] == null) {
                                    return $query->whereDate('job_start_date', Carbon::parse($requestdata['packupdate_end'])->format('Y-m-d'));
                                } elseif (isset($requestdata['startdate_start']) && $requestdata['startdate_start'] != null && !isset($requestdata['startdate_end']) && $requestdata['startdate_end'] == null) {
                                    return $query->whereDate('job_start_date', '>=', Carbon::parse($requestdata['startdate_start'])->format('Y-m-d'))->whereDate('job_start_date', '<=', Carbon::parse($requestdata['startdate_end'])->format('Y-m-d'));
                                }
                            })->get();

            $reports = $reports->map(function ($reports) {
                return $reports->reportformate($reports);
            });
            if ($request->has('order')) {
                $coulmn = $this->jobhistories->reportserchCoulmns[$requestdata['index']];
                if ($requestdata['orderby'] == 'asc') {
                    $reports = $reports->sortByDesc($coulmn);
                } else {
                    $reports = $reports->sortBy($coulmn);
                }
            } else {
                $reports = $reports;
            }

            if ($request->has('start') &&  $request->has('length')) {
                $reports = $this->paginate($reports, $request->start, $request->length);
            }

            if (isset($reports) && count($reports) > 0) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $reports,
                    trans('messages.success'),
                    trans('messages.reports_are_fetch_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.no_reports_found'),
                );
            }
        } catch (\Exception $e) {
            return $this->resultResponse(
                Config::get('rest.http_status_codes.HTTP_BAD_REQUEST'),
                $e->getMessage(),
                trans('messages.error'),
                trans('messages.exception_error'),
            );
        }
    }
}

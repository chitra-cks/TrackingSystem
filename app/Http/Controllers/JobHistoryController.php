<?php

namespace App\Http\Controllers;

use App\Models\Job_history;
use Illuminate\Http\Request;
use App\Traits\RestApi;
use DB;
use Config;
use Validator;
use Auth;
use File;
use ArrayObject;
use Illuminate\Support\Carbon;

class JobHistoryController extends Controller
{
    use RestApi;

    protected $jobhistories;
    public function __construct(Job_history $jobhistories)
    {
        $this->jobhistories = $jobhistories;
        $this->emptyArrayObject = new ArrayObject();
    }

    /**
    * @OA\Post(
    *       path="/api/getjob_histories",
    *       tags={"Job_histories"},
    *       summary="Display a listing of the Job_history",
    *       @OA\RequestBody(
    *           required=false,
    *           @OA\JsonContent(ref="#/components/schemas/JobHistoryRequest")
    *       ),
    *       @OA\Response(
    *           response="200", description="Job history listing  are fetch successfully"
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

        //try {

        $requestdata = $this->datasearch($request->all());
        $job_histories = Job_history::with("Job", "Assignvendor", "Sourcevendor")
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['job']) && $requestdata['job'] != null) {
                    return  $query->where('job_id', $requestdata['job']);
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
                if (isset($requestdata['source_vendor']) && $requestdata['source_vendor'] != null) {
                    return  $query->where('source_vendor_id', $requestdata['source_vendor']);
                }
            })
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['status']) && $requestdata['status'] != null) {
                    return $query->where('status', 'ilike', '%' . $requestdata['status'] . '%');
                }
            })
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['quantity']) && $requestdata['quantity'] != null) {
                    return $query->where('quantity', 'ilike', '%' . $requestdata['quantity'] . '%');
                }
            })
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['design']) && $requestdata['design'] != null) {
                    return  $query->where('design_id', $requestdata['design']);
                }
            })
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['voucher_number']) && $requestdata['voucher_number'] != null) {
                    return $query->where('voucher_number', 'ilike', '%' . $requestdata['voucher_number'] . '%');
                }
            })
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['pickup_date']) && $requestdata['pickup_date'] != null) {
                    return $query->whereDate('pickup_date', Carbon::parse($requestdata['pickup_date'])->format('Y-m-d'));
                }
            })
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['job_start_date']) && $requestdata['job_start_date'] != null) {
                    return $query->whereDate('job_start_date', Carbon::parse($requestdata['job_start_date'])->format('Y-m-d'));
                }
            })
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['job_end_date']) && $requestdata['job_end_date'] != null) {
                    return $query->whereDate('job_end_date', Carbon::parse($requestdata['job_end_date'])->format('Y-m-d'));
                }
            })->get();


        $job_histories = $job_histories->map(function ($job_histories) {
            return $job_histories->format($job_histories);
        });

        if ($request->has('order')) {
            $coulmn = $this->jobhistories->searchCoulmns[$requestdata['index']];
            if ($requestdata['orderby'] == 'asc') {
                $job_histories = $job_histories->sortByDesc($coulmn);
            } else {
                $job_histories = $job_histories->sortBy($coulmn);
            }
        } else {
            $job_histories = $job_histories;
        }

        if ($request->has('start') && $request->has('length')) {
            $job_histories = $this->paginate($job_histories, $request->start, $request->length);
        }
        if (isset($job_histories) && count($job_histories) > 0) {
            return $this->resultResponse(
                Config::get('rest.http_status_codes.HTTP_OK'),
                $job_histories,
                trans('messages.success'),
                trans('messages.job_history_are_fetch_successfully'),
            );
        } else {
            return $this->resultResponse(
                Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                $this->emptyArrayObject,
                trans('messages.error'),
                trans('messages.no_job_histories_found'),
            );
        }
        // } catch (\Exception $e) {
        //     return $this->resultResponse(
        //         Config::get('rest.http_status_codes.HTTP_BAD_REQUEST'),
        //         $e->getMessage(),
        //         trans('messages.error'),
        //         trans('messages.exception_error'),
        //     );
        // }
    }

    /**
    * @OA\Post(
    *       path="/api/job_histories",
    *       tags={"Job_histories"},
    *       summary="Store a newly created Job_history in storage",
    *       @OA\RequestBody(
    *           required=true,
    *           @OA\JsonContent(ref="#/components/schemas/JobHistoryRequest")
    *       ),
    *       @OA\Response(
    *           response="200", description="Job_history title Added successfully"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'assign_vendor_id' => 'required',
                'job_id' => 'required',
                'design_id' => 'required',
                'quantity' => 'required|numeric',
                'voucher_number' => 'required',
                'voucher_bill' => 'mimes:png,pdf|max:2048',
                'pickup_date' => 'required'
            ]);

            if ($validator->fails()) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_BAD_REQUEST'),
                    $validator->errors(),
                    trans('messages.error'),
                    trans('messages.validation_error'),
                );
            }
            $requestData = $request->all();
            $requestData['ip_address']  = $request->ip();
            $requestData['status'] = 'inprocess';
            $requestData['pickup_date'] = Carbon::parse($request->pickup_date)->format('Y-m-d');
            if (isset($request->job_start_date) && $request->job_start_date != '' && $request->job_start_date != null) {
                $requestData['job_start_date'] = Carbon::parse($request->job_start_date)->format('Y-m-d');
            }
            if (isset($request->job_end_date) && $request->job_end_date != '' && $request->job_end_date != null) {
                $requestData['job_end_date'] = Carbon::parse($request->job_end_date)->format('Y-m-d');
            }

            if ($request->hasFile('voucher_bill')) {
                $filedata = $this->fileUpload($request->file('voucher_bill'));
                if (isset($filedata) && $filedata != '') {
                    $requestData['voucher_bill'] = $filedata['name'];
                    $requestData['voucher_bill_mimetype'] = $filedata['type'];
                }
            }
            $job_history=Job_history::create($requestData);

            if (isset($job_history) && $job_history != '') {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $job_history,
                    trans('messages.success'),
                    trans('messages.job_history_add_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.not_able_to_add_job_history'),
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

    /**
    * @OA\Get(
    *       path="/api/job_histories/{job_history}",
    *       tags={"Job_histories"},
    *       summary="Display the specified job_history",
    *       @OA\Response(
    *           response="200", description="Job_history fetch successfully"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */

    public function show(Job_history $job_history)
    {
        try {
            if ($job_history) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $job_history,
                    trans('messages.success'),
                    trans('messages.job_history_fetch_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.no_job_history_found'),
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

    /**
    * @OA\Put(
    *       path="/api/job_histories/{job_history}",
    *       tags={"Job_histories"},
    *       summary="Update job_history in storage",
    *       @OA\RequestBody(
    *           required=true,
    *           @OA\JsonContent(ref="#/components/schemas/JobHistoryRequest")
    *       ),
    *       @OA\Response(
    *           response="200", description="Job_history  Updated successfully"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */
    public function update(Request $request, Job_history $job_history)
    {
        try {
            $validator = Validator::make($request->all(), [
                    'assign_vendor_id' => 'required',
                    'job_id' => 'required',
                    'design_id' => 'required',
                    'quantity' => 'required|numeric',
                    'voucher_number' => 'required',
                    'voucher_bill' => 'mimes:png,pdf|max:2048',
                    'pickup_date' => 'required'
                ]);

            if ($validator->fails()) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_BAD_REQUEST'),
                    $validator->errors(),
                    trans('messages.error'),
                    trans('messages.validation_error'),
                );
            }
            $requestData = $request->all();
            $requestData['pickup_date'] = Carbon::parse($request->pickup_date)->format('Y-m-d');
            if (isset($request->job_start_date) && $request->job_start_date != '' && $request->job_start_date != null) {
                $requestData['job_start_date'] = Carbon::parse($request->job_start_date)->format('Y-m-d');
            }
            if (isset($request->job_end_date) && $request->job_end_date != '' && $request->job_end_date != null) {
                $requestData['job_end_date'] = Carbon::parse($request->job_end_date)->format('Y-m-d');
            }
            if ($request->hasFile('voucher_bill')) {
                $this->filedelete($job_history->voucher_bill);
                $filedata = $this->fileUpload($request->file('voucher_bill'));
                if (isset($filedata) && $filedata != '') {
                    $requestData['voucher_bill'] = $filedata['name'];
                    $requestData['voucher_bill_mimetype'] = $filedata['type'];
                }
            }

            $jobhistory = $job_history->update($requestData);
            if ($jobhistory) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $jobhistory,
                    trans('messages.success'),
                    trans('messages.job_history_update_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.job_history_not_updated'),
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

    /**

    *@OA\Delete(
    *       path="/api/job_histories/{job_history}",
    *       tags={"Job_histories"},
    *       summary="Delete Job_history in storage",
    *       @OA\Response(
    *           response="200", description="Job_history Deleted successfully"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */
    public function destroy(Job_history $job_history)
    {
        try {
            $job_history->delete();
            if ($job_history) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $job_history,
                    trans('messages.success'),
                    trans('messages.job_history_deleted_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.job_history_not_deleted'),
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
    protected function fileUpload($file)
    {
        try {
            if (isset($file) && $file != null) {
                $fileName = time().'_'.$file->getClientOriginalName();
                $filePath = $file->storeAs('voucher_bill', $fileName, 'public');
                $type = $file->getMimeType();
                $filedata['name'] = $fileName;
                $filedata['type'] = $type;
                return $filedata;
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
    protected function filedelete($filename)
    {
        try {
            if (isset($filename) && $filename != '' && file_exists(config('constants.voucher_bill_path').$filename)) {
                unlink(config('constants.voucher_bill_path').$filename);
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

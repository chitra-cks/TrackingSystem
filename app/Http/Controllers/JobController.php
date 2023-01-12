<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use App\Traits\RestApi;
use DB;
use Config;
use Validator;
use Auth;
use File;
use ArrayObject;
use Illuminate\Pagination\LengthAwarePaginator;

class JobController extends Controller
{
    use RestApi;
    protected $jobs;
    public function __construct(Job $jobs)
    {
        $this->jobs = $jobs;
        $this->emptyArrayObject = new ArrayObject();
    }

    /**
    * @OA\Post(
    *       path="/api/getjobs",
    *       tags={"Jobs"},
    *       summary="Display a listing of the Job process",
    *       @OA\RequestBody(
    *           required=false,
    *           @OA\JsonContent(ref="#/components/schemas/JobRequest")
    *       ),
    *       @OA\Response(
    *           response="200", description="Job listings  are fetch successfully"
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
            $requestdata= $this->datasearch($request->all());
            $job =  Job::where(function ($query) use ($requestdata) {
                if (isset($requestdata['title']) && $requestdata['title'] != null && !isset($requestdata['status']) && $requestdata['status'] == null) {
                    return $query->where('title', 'ilike', '%' . $requestdata['title'] . '%');
                } elseif (isset($requestdata['status']) && $requestdata['status'] != null && !isset($requestdata['title']) && $requestdata['title'] == null) {
                    return $query->where('status', 'ilike', '%' . $requestdata['status'] . '%');
                } elseif (isset($requestdata['title']) && $requestdata['title'] != null && isset($requestdata['status']) && $requestdata['status'] != null) {
                    return $query->where('title', 'ilike', '%' . $requestdata['title'] . '%')->where('status', 'ilike', '%' . $requestdata['status'] . '%');
                }
            })->get();

            $job = $job->map(function ($job) {
                return $job->format($job);
            });

            if ($request->has('order')) {
                $coulmn = $this->jobs->searchCoulmns[$requestdata['index']];
                if ($requestdata['orderby'] == 'asc') {
                    $job = $job->sortByDesc($coulmn);
                } else {
                    $job = $job->sortBy($coulmn);
                }
            } else {
                $job = $job;
            }
            if ($request->has('start') && $request->has('length')) {
                $job = $this->paginate($job, $request->start, $request->length);
            }
            if (isset($job) && count($job) > 0) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $job,
                    trans('messages.success'),
                    trans('messages.job_are_fetch_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.no_jobs_found'),
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
    * @OA\Post(
    *       path="/api/jobs",
    *       tags={"Jobs"},
    *       summary="Store a newly created jobs in storage",
    *       @OA\RequestBody(
    *           required=true,
    *           @OA\JsonContent(ref="#/components/schemas/JobRequest")
    *       ),
    *       @OA\Response(
    *           response="200", description="Job title Added successfully"
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
                'title' => 'required|max:100|unique:jobs,title',
                'status' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_BAD_REQUEST'),
                    $validator->errors(),
                    trans('messages.error'),
                    trans('messages.validation_error'),
                );
            }
            $request['title'] = ucwords(strtolower($request['title']));
            $request['ip_address']  = $request->ip();

            $job = Job::create($request->all());

            if ($job) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $job,
                    trans('messages.success'),
                    trans('messages.job_add_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.not_able_to_add_job_title'),
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
    *       path="/api/jobs/{job}",
    *       tags={"Jobs"},
    *       summary="Display the specified job",
    *       @OA\Response(
    *           response="200", description="Job fetch successfully"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */

    public function show(Job $job)
    {
        try {
            if (isset($job) && $job != '') {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $job,
                    trans('messages.success'),
                    trans('messages.job_fetch_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.no_job_found'),
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
    *       path="/api/jobs/{job}",
    *       tags={"Jobs"},
    *       summary="Update jobs in storage",
    *       @OA\RequestBody(
    *           required=true,
    *           @OA\JsonContent(ref="#/components/schemas/JobRequest")
    *       ),
    *       @OA\Response(
    *           response="200", description="Job title Updated successfully"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */

    public function update(Request $request, Job $job)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|max:100|unique:jobs,title,'.$job->id,
                'status' => 'required',
            ]);

            if ($validator->fails()) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_BAD_REQUEST'),
                    $validator->errors(),
                    trans('messages.error'),
                    trans('messages.validation_error'),
                );
            }
            $request['title'] = ucwords(strtolower($request['title']));
            $jobs = $job->update($request->all());
            if ($jobs) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $jobs,
                    trans('messages.success'),
                    trans('messages.job_update_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.job_not_updated'),
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
    *       path="/api/jobs/{job}",
    *       tags={"Jobs"},
    *       summary="Delete jobs in storage",
    *       @OA\Response(
    *           response="200", description="Job Deleted successfully"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */

    public function destroy(Job $job)
    {
        try {
            $job->delete();
            if ($job) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $job,
                    trans('messages.success'),
                    trans('messages.job_deleted_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.job_not_deleted'),
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
    *       path="/api/getEnableJobs",
    *       tags={"Jobs"},
    *       summary="Display the  job list",
    *       @OA\Response(
    *           response="200", description="Job fetch successfully"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */

    public function getEnableJob()
    {
        try {
            $job = Job::select('id', 'title')
                       ->where('status', 'enable')
                       ->get();

            if (isset($job) && count($job) > 0) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $job,
                    trans('messages.success'),
                    trans('messages.job_are_fetch_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.no_jobs_found'),
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

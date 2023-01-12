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

class DashboardController extends Controller
{
    use RestApi;
    public function __construct()
    {
        $this->emptyArrayObject = new ArrayObject();
    }
    /**
    * @OA\Get(
    *       path="/api/total-list",
    *       tags={"Dashboard"},
    *       summary="Display Total # of Sarees at  Vendor.",
    *       @OA\Response(
    *           response="200", description="Display Total # of Sarees at  Vendor. fetch successfully"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */
    public function getTotalSareesAtVendor()
    {
        try {

            // saree at each vendor type
            $saree_at_each_vendor_type = Job_history::groupBy('assign_vendor_id')
            ->selectRaw('assign_vendor_id,sum(quantity) as quantity')
            ->has('Assignvendor')
            ->with('Assignvendor')
            ->get();

            $saree_at_each_vendor_type = $saree_at_each_vendor_type->map(function ($sareeEachVendorType) {
                return [
                    'id'            => $sareeEachVendorType->assign_vendor_id,
                    'fullname'     => $sareeEachVendorType->assignvendor->firstname.' '.$sareeEachVendorType->assignvendor->lastname,
                    'vendor_type' => isset($sareeEachVendorType->assignvendor->job->title) ? $sareeEachVendorType->assignvendor->job->title : '',
                    'quantity'      => $sareeEachVendorType->quantity,
                ];
            });

            $responsedata['saree_at_each_vendor_type'] = $saree_at_each_vendor_type;

            //saree at design each vendor type
            $saree_at_design_each_vendor_type = Job_history::groupBy('design_id')
            ->selectRaw('design_id,sum(quantity) as quantity')
            ->has('Design')
            ->with('Design')
            ->get();

            $saree_at_design_each_vendor_type = $saree_at_design_each_vendor_type->map(function ($sareeDesignEachVendorType) {
                return [
                    'id'            => $sareeDesignEachVendorType->design_id,
                    'title'         => $sareeDesignEachVendorType->design->title ,
                    'design_no'      => $sareeDesignEachVendorType->design->design_no,
                    'quantity'      => $sareeDesignEachVendorType->quantity,
                ];
            });

            $responsedata['saree_at_design_each_vendor_type'] = $saree_at_design_each_vendor_type;

            // saree vendor type
            $saree_vendor_type = Job_history::groupBy('job_id')
            ->selectRaw('job_id,sum(quantity) as quantity')
            ->with('Job')
            ->has('Job')
            ->get();
            $saree_vendor_type = $saree_vendor_type->map(function ($sareevendortype) {
                return [
                    'id'            => $sareevendortype->job_id,
                    'title'         => $sareevendortype->job->title,
                    'quantity'     => $sareevendortype->quantity,
                ];
            });
            $responsedata['saree_vendor_type'] = $saree_vendor_type;

            //saree design type
            $saree_design_type = Job_history::groupBy('design_id')
            ->selectRaw('design_id,sum(quantity) as quantity')
            ->has('Design')
            ->with('Design')
            ->get();

            $saree_design_type = $saree_design_type->map(function ($sareedesigntype) {
                return [
                    'id'            => $sareedesigntype->design_id,
                    'title'         => $sareedesigntype->Design->title,
                    'quantity'     => $sareedesigntype->quantity,
                ];
            });

            $responsedata['saree_design_type'] = $saree_design_type;

            if (isset($responsedata) && $responsedata != '') {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $responsedata,
                    trans('messages.success'),
                    trans('messages.total_saree_vendor_fetch_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.total_saree_vendor_not_found'),
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

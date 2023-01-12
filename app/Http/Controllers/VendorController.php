<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;
use App\Traits\RestApi;
use DB;
use Config;
use Validator;
use Auth;
use File;
use ArrayObject;

class VendorController extends Controller
{
    use RestApi;
    protected $vendors;
    public function __construct(Vendor $vendors)
    {
        $this->vendors = $vendors;
        $this->emptyArrayObject = new ArrayObject();
    }


    /**
    * @OA\Post(
    *       path="/api/getvendors",
    *       tags={"Vendors"},
    *       summary="Display a listing of the Vendor",
    *       @OA\RequestBody(
    *           required=false,
    *           @OA\JsonContent(ref="#/components/schemas/VendorRequest")
    *       ),
    *       @OA\Response(
    *           response="200", description="Vendor listing  are fetch successfully"
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
            $vendor = Vendor::where(function ($query) use ($requestdata) {
                if (isset($requestdata['vendor']) && $requestdata['vendor'] != null) {
                    return $query->whereRaw("concat(firstname, ' ', lastname) ilike '%" . $requestdata['vendor'] . "%' ");
                }
            })
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['mobile']) && $requestdata['mobile'] != null) {
                    return $query->where('mobile', 'ilike', '%' . $requestdata['mobile'] . '%');
                }
            })
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['status']) && $requestdata['status'] != null) {
                    return $query->where('status', 'ilike', '%' . $requestdata['status'] . '%');
                }
            })
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['job']) && $requestdata['job'] != null) {
                    return $query->where('job_id', $requestdata['job']);
                }
            })
            ->get();

            $vendor = $vendor->map(function ($vendor) {
                return $vendor->format($vendor);
            });
            if ($request->has('order')) {
                $coulmn = $this->vendors->searchCoulmns[$requestdata['index']];
                if ($requestdata['orderby'] == 'asc') {
                    $vendor = $vendor->sortByDesc($coulmn);
                } else {
                    $vendor = $vendor->sortBy($coulmn);
                }
            } else {
                $vendor = $vendor;
            }

            if ($request->has('start') && $request->has('length')) {
                $vendor = $this->paginate($vendor, $request->start, $request->length);
            }
            if (isset($vendor) && count($vendor) > 0) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $vendor,
                    trans('messages.success'),
                    trans('messages.vendor_listing_are_fetch_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.not_able_to_get_fetch_vendor'),
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
    *       path="/api/vendors",
    *       tags={"Vendors"},
    *       summary="Store a newly created vendore in storage",
    *       @OA\RequestBody(
    *           required=true,
    *           @OA\JsonContent(ref="#/components/schemas/VendorRequest")
    *       ),
    *       @OA\Response(
    *           response="200", description="Vendor Added successfully"
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
                'firstname'    => 'required|max:50',
                'lastname'     => 'required|max:50',
                'mobile'       => 'required|numeric|digits:10|unique:vendors',
                'price'        => 'required',
                'status'       => 'required',
            ]);

            if ($validator->fails()) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_BAD_REQUEST'),
                    $validator->errors(),
                    trans('messages.error'),
                    trans('messages.validation_error'),
                );
            }

            $request['ip_address']  = $request->ip();
            $request['firstname'] = strtolower($request['firstname']);
            $request['lastname'] = strtolower($request['lastname']);
            $vendor=Vendor::create($request->all());

            if ($vendor) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $vendor,
                    trans('messages.success'),
                    trans('messages.vendor_add_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.not_able_to_add_vendor'),
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
    *       path="/api/vendors/{vendor}",
    *       tags={"Vendors"},
    *       summary="Display the specified vendor",
    *       @OA\Response(
    *           response="200", description="Vendor fetch successfully"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */

    public function show(Vendor $vendor)
    {
        try {
            if ($vendor) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $vendor,
                    trans('messages.success'),
                    trans('messages.vendor_fetch_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.not_able_to_fetch_list'),
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
    *       path="/api/vendors/{vendor}",
    *       tags={"Vendors"},
    *       summary="Update vendors in storage",
    *       @OA\RequestBody(
    *           required=true,
    *           @OA\JsonContent(ref="#/components/schemas/VendorRequest")
    *       ),
    *       @OA\Response(
    *           response="200", description="Vendor  Updated successfully"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */
    public function update(Request $request, Vendor $vendor)
    {
        try {
            $validator = Validator::make($request->all(), [
                'firstname'    => 'required|max:50',
                'lastname'     => 'required|max:50',
                'mobile'       => 'required|numeric|digits:10|unique:vendors,mobile,'.$vendor->id,
                'price'        => 'required',
                'status'       => 'required',
                ]);

            if ($validator->fails()) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_BAD_REQUEST'),
                    $validator->errors(),
                    trans('messages.error'),
                    trans('messages.validation_error'),
                );
            }
            $request['firstname'] = strtolower($request['firstname']);
            $request['lastname'] = strtolower($request['lastname']);
            $vendors = $vendor->update($request->all());
            if ($vendors) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $vendors,
                    trans('messages.success'),
                    trans('messages.vendor_update_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.vendor_not_updated'),
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
    *       path="/api/vendors/{vendor}",
    *       tags={"Vendors"},
    *       summary="Delete vendors in storage",
    *       @OA\Response(
    *           response="200", description="Vendor Deleted successfully"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */

    public function destroy(Vendor $vendor)
    {
        try {
            $vendor->delete();
            if ($vendor) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $vendor,
                    trans('messages.success'),
                    trans('messages.vendor_deleted_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.vendor_not_deleted'),
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
    *       path="/api/getEnableVendors",
    *       tags={"Vendors"},
    *       summary="Display the  vendor list",
    *       @OA\Response(
    *           response="200", description="Vendor fetch successfully"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */

    public function getEnableVendor()
    {
        try {
            $vendor = Vendor::select('id', DB::raw("CONCAT(vendors.firstname,' ' ,vendors.lastname) AS fullname"))
                      ->where('status', 'enable')
                      ->get();
            if (isset($vendor) && count($vendor) > 0) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $vendor,
                    trans('messages.success'),
                    trans('messages.vendor_listing_are_fetch_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.not_able_to_get_fetch_vendor'),
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
    *       path="/api/getvendorsbyjob/{$id}",
    *       tags={"Vendors"},
    *       summary="Display the  vendor list by job",
    *       @OA\Response(
    *           response="200", description="Vendor fetch successfully"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */
    public function getvendorsbyjob($id)
    {
        try {
            $vendors = Vendor::select('id', 'job_id', DB::raw("CONCAT(vendors.firstname,' ' ,vendors.lastname) AS fullname"))->where('job_id', $id)->get();
            if (isset($vendors) && count($vendors) > 0) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $vendors,
                    trans('messages.success'),
                    trans('messages.vendor_fetch_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.not_able_to_get_fetch_vendor'),
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

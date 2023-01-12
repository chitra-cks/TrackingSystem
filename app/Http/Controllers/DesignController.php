<?php

namespace App\Http\Controllers;

use App\Models\Design;
use Illuminate\Http\Request;
use App\Traits\RestApi;
use DB;
use Config;
use Validator;
use Auth;
use File;
use ArrayObject;

class DesignController extends Controller
{
    use RestApi;
    protected $designs;
    public function __construct(Design $designs)
    {
        $this->designs = $designs;
        $this->emptyArrayObject = new ArrayObject();
    }

    /**
    * @OA\Post(
    *       path="/api/getdesigns",
    *       tags={"Designs"},
    *       summary="Display a listing of the Design",
    *       @OA\RequestBody(
    *           required=false,
    *           @OA\JsonContent(ref="#/components/schemas/DesignRequest")
    *       ),
    *       @OA\Response(
    *           response="200", description="Design listing  are fetch successfully"
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
            $design = Design::where(function ($query) use ($requestdata) {
                if (isset($requestdata['title']) && $requestdata['title'] != null) {
                    return $query->where('title', 'ilike', '%' . $requestdata['title'] . '%');
                }
            })
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['design_no']) && $requestdata['design_no'] != null) {
                    return $query->where('design_no', 'ilike', '%' . $requestdata['design_no'] . '%');
                }
            })
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['status']) && $requestdata['status'] != null) {
                    return $query->where('status', 'ilike', '%' . $requestdata['status'] . '%');
                }
            })->get();

            $design = $design->map(function ($design) {
                return $design->format($design);
            });

            if ($request->has('order')) {
                $coulmn = $this->designs->searchCoulmns[$requestdata['index']];
                if ($requestdata['orderby'] == 'asc') {
                    $design = $design->sortByDesc($coulmn);
                } else {
                    $design = $design->sortBy($coulmn);
                }
            } else {
                $design = $design;
            }

            if ($request->has('start') && $request->has('length')) {
                $design = $this->paginate($design, $request->start, $request->length);
            }



            if (isset($design) && count($design) > 0) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $design,
                    trans('messages.success'),
                    trans('messages.design_listing_are_fetch_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.not_able_to_get_fetch_designs'),
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
    *       path="/api/designs",
    *       tags={"Designs"},
    *       summary="Store a newly created design in storage",
    *       @OA\RequestBody(
    *           required=true,
    *           @OA\JsonContent(ref="#/components/schemas/DesignRequest")
    *       ),
    *       @OA\Response(
    *           response="200", description="Design Added successfully"
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
                'title'    => 'required|max:100|unique:designs,title',
                'design_no'=> 'required',
                'status'   => 'required',
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
            $request['title'] = ucwords(strtolower($request['title']));
            $design=Design::create($request->all());
            if ($design) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $design,
                    trans('messages.success'),
                    trans('messages.design_add_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.not_able_to_add_design'),
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
    *       path="/api/designs/{design}",
    *       tags={"Designs"},
    *       summary="Display the specified design",
    *       @OA\Response(
    *           response="200", description="Design fetch successfully"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */

    public function show(Design $design)
    {
        try {
            if ($design) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $design,
                    trans('messages.success'),
                    trans('messages.design_fetch_successfully'),
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
     *       path="/api/designs/{design}",
     *       tags={"Designs"},
     *       summary="Update designs in storage",
     *       @OA\RequestBody(
     *           required=true,
     *           @OA\JsonContent(ref="#/components/schemas/DesignRequest")
     *       ),
     *       @OA\Response(
     *           response="200", description="Design  Updated successfully"
     *       ),
     *       @OA\Response(
     *           response="400", description="Validation error"
     *       ),
     *       @OA\Response(
     *           response="500", description="Internal server error"
     *       )
     *   )
     */
    public function update(Request $request, Design $design)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title'    => 'required|max:100|unique:designs,title,'.$design->id,
                'design_no'=> 'required',
                'status'   => 'required',
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
            $designs = $design->update($request->all());
            if ($designs) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $designs,
                    trans('messages.success'),
                    trans('messages.design_update_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.design_not_updated'),
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
    *       path="/api/designs/{design}",
    *       tags={"Designs"},
    *       summary="Delete designs in storage",
    *       @OA\Response(
    *           response="200", description="Design Deleted successfully"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */

    public function destroy(Design $design)
    {
        try {
            $design->delete();
            if ($design) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $design,
                    trans('messages.success'),
                    trans('messages.design_deleted_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.design_not_deleted'),
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
    *       path="/api/getEnableDesigns",
    *       tags={"Designs"},
    *       summary="Display the  design list",
    *       @OA\Response(
    *           response="200", description="Design fetch successfully"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */

    public function getEnableDesign()
    {
        try {
            $design = Design::select('id', 'title')
                      ->where('status', 'enable')
                      ->get();
            if (isset($design) && count($design) > 0) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $design,
                    trans('messages.success'),
                    trans('messages.design_listing_are_fetch_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.not_able_to_get_fetch_designs'),
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

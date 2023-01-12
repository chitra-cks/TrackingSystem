<?php

namespace App\Http\Controllers;

use App\Models\Raw_material;
use Illuminate\Http\Request;
use App\Traits\RestApi;
use DB;
use Config;
use Validator;
use Auth;
use File;
use ArrayObject;
use Illuminate\Support\Carbon;

class RawMaterialController extends Controller
{
    use RestApi;
    protected $raw_materials;
    public function __construct(Raw_material $raw_materials)
    {
        $this->raw_materials = $raw_materials;
        $this->emptyArrayObject = new ArrayObject();
    }

    /**
    * @OA\Post(
    *       path="/api/getrawmaterials",
    *       tags={"RawMaterials"},
    *       summary="Display  listing of the rawmaterials",
    *       @OA\RequestBody(
    *           required=false,
    *           @OA\JsonContent(ref="#/components/schemas/RawMaterialRequest")
    *       ),
    *       @OA\Response(
    *           response="200", description="Raw Material listing  are fetch successfully"
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
            $rawmaterial = Raw_material::where(function ($query) use ($requestdata) {
                if (isset($requestdata['source']) && $requestdata['source'] != null) {
                    return $query->where('source', 'ilike', '%' . $requestdata['source'] . '%');
                }
            })
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['price']) && $requestdata['price'] != null) {
                    return $query->where('price', 'ilike', '%' . $requestdata['price'] . '%');
                }
            })
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['quantity']) && $requestdata['quantity'] != null) {
                    return $query->where('quantity', 'ilike', '%' . $requestdata['quantity'] . '%');
                }
            })
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['voucher_number']) && $requestdata['voucher_number'] != null) {
                    return $query->where('voucher_number', 'ilike', '%' . $requestdata['voucher_number'] . '%');
                }
            })
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['LR_number']) && $requestdata['LR_number'] != null) {
                    return $query->where('LR_number', 'ilike', '%' . $requestdata['LR_number'] . '%');
                }
            })
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['status']) && $requestdata['status'] != null) {
                    return $query->where('status', 'ilike', '%' . $requestdata['status'] . '%');
                }
            })
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['pickup_date']) && $requestdata['pickup_date'] != null) {
                    return $query->whereDate('pickup_date', Carbon::parse($requestdata['pickup_date'])->format('Y-m-d'));
                }
            })->get();
            $rawmaterial = $rawmaterial->map(function ($rawmaterial) {
                return $rawmaterial->format($rawmaterial);
            });
            if ($request->has('order')) {
                $coulmn = $this->raw_materials->searchCoulmns[$requestdata['index']];
                if($requestdata['orderby'] == 'asc')
                {
                    $rawmaterial = $rawmaterial->sortByDesc($coulmn);
                }
                else
                {
                    $rawmaterial = $rawmaterial->sortBy($coulmn);
                }     
            }
            else
            {
                $rawmaterial = $rawmaterial;
            }
            if ($request->has('start') && $request->has('length')) {
                $rawmaterial = $this->paginate($rawmaterial, $request->start, $request->length);
            }
            if (isset($rawmaterial)  && count($rawmaterial) > 0) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $rawmaterial,
                    trans('messages.success'),
                    trans('messages.raw_material_are_fetch_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.not_able_to_get_fetch_rawmaterials'),
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
    *       path="/api/rawmaterials",
    *       tags={"RawMaterials"},
    *       summary="Store a newly created rawmaterials in storage",
    *       @OA\RequestBody(
    *           required=true,
    *           @OA\JsonContent(ref="#/components/schemas/RawMaterialRequest")
    *       ),
    *       @OA\Response(
    *           response="200", description="RawMaterial Added successfully"
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
            'quantity'        => 'required',
            'source'          => 'required',
            'price'           => 'required',
            'voucher_number'  => 'required',
            'voucher'         => 'mimes:jpeg,png,jpg,pdf|max:2048',
            'LR_number'       => 'required',
            'LR'              => 'mimes:jpeg,png,jpg,pdf|max:2048',
            'status'          => 'required',
            'pickup_date'     => 'required',
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
            if ($request->hasFile('voucher')) {
                $filedata = $this->fileUpload($request->file('voucher'), 'voucher');
                if (isset($filedata) && $filedata != '') {
                    $requestData['voucher'] = $filedata['name'];
                    $requestData['voucher_mimetype'] = $filedata['type'];
                }
            }
            if ($request->hasFile('LR')) {
                $filedata = $this->fileUpload($request->file('LR'), 'LR');
                if (isset($filedata) && $filedata != '') {
                    $requestData['LR'] = $filedata['name'];
                    $requestData['LR_mimetype'] = $filedata['type'];
                }
            }
            $requestData['ip_address']  = $request->ip();

            $rawmaterial=Raw_material::create($requestData);
            if ($rawmaterial) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $rawmaterial,
                    trans('messages.success'),
                    trans('messages.rawmaterial_add_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.not_able_to_add_rawmaterial'),
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
     *       path="/api/rawmaterials/{rawmaterial}",
     *       tags={"RawMaterials"},
     *       summary="Display the specified rawmaterials",
     *       @OA\Response(
     *           response="200", description="RawMaterial fetch successfully"
     *       ),
     *       @OA\Response(
     *           response="400", description="Validation error"
     *       ),
     *       @OA\Response(
     *           response="500", description="Internal server error"
     *       )
     *   )
     */
    public function show(Raw_material $rawmaterial)
    {
        try {
            if ($rawmaterial) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $rawmaterial,
                    trans('messages.success'),
                    trans('messages.rawmaterial_fetch_successfully'),
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
     *       path="/api/rawmaterials/{rawmaterial}",
     *       tags={"RawMaterials"},
     *       summary="Update rawMaterials in storage",
     *       @OA\RequestBody(
     *           required=true,
     *           @OA\JsonContent(ref="#/components/schemas/RawMaterialRequest")
     *       ),
     *       @OA\Response(
     *           response="200", description="RawMaterials  Updated successfully"
     *       ),
     *       @OA\Response(
     *           response="400", description="Validation error"
     *       ),
     *       @OA\Response(
     *           response="500", description="Internal server error"
     *       )
     *   )
     */
    public function update(Request $request, Raw_material $rawmaterial)
    {
        $validator = Validator::make($request->all(), [
            'quantity'        => 'required',
            'source'          => 'required',
            'price'           => 'required',
            'voucher_number'  => 'required',
            'voucher'         => 'mimes:jpeg,png,jpg,pdf|max:2048',
            'LR_number'       => 'required',
            'LR'              => 'mimes:jpeg,png,jpg,pdf|max:2048',
            'status'          => 'required',
            'pickup_date'     => 'required',
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
        if ($request->hasFile('voucher')) {
            $this->filedelete($rawmaterial->voucher, 'voucher');
            $filedata = $this->fileUpload($request->file('voucher'), 'voucher');
            if (isset($filedata) && $filedata != '') {
                $requestData['voucher'] = $filedata['name'];
                $requestData['voucher_mimetype'] = $filedata['type'];
            }
        }
        if ($request->hasFile('LR')) {
            $this->filedelete($rawmaterial->LR, 'LR');
            $filedata = $this->fileUpload($request->file('LR'), 'LR');
            if (isset($filedata) && $filedata != '') {
                $requestData['LR'] = $filedata['name'];
                $requestData['LR_mimetype'] = $filedata['type'];
            }
        }
        $rawmaterials = $rawmaterial->update($request->all());
        if ($rawmaterials) {
            return $this->resultResponse(
                Config::get('rest.http_status_codes.HTTP_OK'),
                $rawmaterials,
                trans('messages.success'),
                trans('messages.rawmaterial_update_successfully'),
            );
        } else {
            return $this->resultResponse(
                Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                $this->emptyArrayObject,
                trans('messages.success'),
                trans('messages.rawmaterial_not_updated'),
            );
        }
    }

    /**

    *@OA\Delete(
    *       path="/api/rawmaterials/{rawmaterial}",
    *       tags={"RawMaterials"},
    *       summary="Delete rawmaterials in storage",
    *       @OA\Response(
    *           response="200", description="RawMaterials Deleted successfully"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */
    public function destroy(Raw_material $rawmaterial)
    {
        try {
            $rawmaterial->delete();
            if ($rawmaterial) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $rawmaterial,
                    trans('messages.success'),
                    trans('messages.rawmaterial_deleted_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.rawmaterial_not_deleted'),
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

    protected function fileUpload($file, $folder)
    {
        try {
            if (isset($file) && $file != null) {
                $fileName = time().'_'.$file->getClientOriginalName();
                $filePath = $file->storeAs($folder, $fileName, 'public');
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
    protected function filedelete($filename, $folder)
    {
        try {
            if ($folder == 'voucher') {
                if (isset($filename) && $filename != '' && file_exists(config('constants.voucher_path').$filename)) {
                    unlink(config('constants.voucher_path').$filename);
                }
            } else {
                if (isset($filename) && $filename != '' && file_exists(config('constants.lr_path').$filename)) {
                    unlink(config('constants.lr_path').$filename);
                }
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

<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Traits\RestApi;
use DB;
use Config;
use Validator;
use Auth;
use File;
use ArrayObject;

class RoleController extends Controller
{
    use RestApi;
    protected $roles;
    public function __construct(Role $roles)
    {
        $this->roles = $roles;
        $this->emptyArrayObject = new ArrayObject();
    }

    /**
    * @OA\Post(
    *       path="/api/getroles",
    *       tags={"Roles"},
    *       summary="Display a listing of the Role",
    *       @OA\RequestBody(
    *           required=false,
    *           @OA\JsonContent(ref="#/components/schemas/RoleRequest")
    *       ),
    *       @OA\Response(
    *           response="200", description="Role listing  are fetch successfully"
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
            $role = Role::where(function ($query) use ($requestdata) {
                if (isset($requestdata['name']) && $requestdata['name'] != null) {
                    return $query->where('name', 'ilike', '%' . $requestdata['name'] . '%');
                }
            })->where('name', '!=', 'Super Admin')->get();

            $role = $role->map(function ($role) {
                return $role->format($role);
            });


            if ($request->has('order')) {
                $coulmn = $this->roles->searchCoulmns[$requestdata['index']];
                if ($requestdata['orderby'] == 'asc') {
                    $role = $role->sortByDesc($coulmn);
                } else {
                    $role = $role->sortBy($coulmn);
                }
            } else {
                $role = $role;
            }

            if ($request->has('start') && $request->has('length')) {
                $role = $this->paginate($role, $request->start, $request->length);
            }
            if (isset($role) && count($role) > 0) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $role,
                    trans('messages.success'),
                    trans('messages.role_listing_are_fetch_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.not_able_to_get_fetch_roles'),
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
    *       path="/api/roles",
    *       tags={"Roles"},
    *       summary="Store a newly created role in storage",
    *       @OA\RequestBody(
    *           required=true,
    *           @OA\JsonContent(ref="#/components/schemas/RoleRequest")
    *       ),
    *       @OA\Response(
    *           response="200", description="Role Added successfully"
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
                'name'    => 'required|max:100',
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
            $role=Role::create($request->all());
            if ($role) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $role,
                    trans('messages.success'),
                    trans('messages.role_add_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.not_able_to_add_role'),
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
    *       path="/api/roles/{role}",
    *       tags={"Roles"},
    *       summary="Display the specified role",
    *       @OA\Response(
    *           response="200", description="Role fetch successfully"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */


    public function show(Role $role)
    {
        try {
            if ($role) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $role,
                    trans('messages.success'),
                    trans('messages.role_fetch_successfully'),
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
     *       path="/api/roles/{role}",
     *       tags={"Roles"},
     *       summary="Update roles in storage",
     *       @OA\RequestBody(
     *           required=true,
     *           @OA\JsonContent(ref="#/components/schemas/RoleRequest")
     *       ),
     *       @OA\Response(
     *           response="200", description="Role  Updated successfully"
     *       ),
     *       @OA\Response(
     *           response="400", description="Validation error"
     *       ),
     *       @OA\Response(
     *           response="500", description="Internal server error"
     *       )
     *   )
     */


    public function update(Request $request, Role $role)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'    => 'required|max:100',

            ]);

            if ($validator->fails()) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_BAD_REQUEST'),
                    $validator->errors(),
                    trans('messages.error'),
                    trans('messages.validation_error'),
                );
            }

            $roles = $role->update($request->all());
            if ($roles) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $roles,
                    trans('messages.success'),
                    trans('messages.role_update_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.role_not_updated'),
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
    *       path="/api/roles/{role}",
    *       tags={"Roles"},
    *       summary="Delete roles in storage",
    *       @OA\Response(
    *           response="200", description="Role Deleted successfully"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */

    public function destroy(Role $role)
    {
        try {
            $role->delete();
            if ($role) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $role,
                    trans('messages.success'),
                    trans('messages.role_deleted_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.role_not_deleted'),
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

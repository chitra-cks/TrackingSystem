<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\RestApi;
use DB;
use Config;
use Validator;
use Auth;
use File;
use ArrayObject;
use Hash;
use Collection;

class UserController extends Controller
{
    use RestApi;
    protected $users;
    public function __construct(User $users)
    {
        $this->users = $users;
        $this->emptyArrayObject = new ArrayObject();
    }
    /**
    * @OA\Post(
    *       path="/api/getusers",
    *       tags={"Users"},
    *       summary="Display  listing of the Users",
    *       @OA\RequestBody(
    *           required=false,
    *           @OA\JsonContent(ref="#/components/schemas/UserRequest")
    *       ),
    *       @OA\Response(
    *           response="200", description="Users listing  are fetch successfully"
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
            $user =  User::with('Role')
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['role_id']) && $requestdata['role_id'] != null) {
                    return $query->where('role_id', $requestdata['role_id']);
                }
            })
            ->whereHas('Role', function ($query) use ($requestdata) {
                $query->where('name', '!=', 'Super Admin');
            })
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['user']) && $requestdata['user'] != null) {
                    return $query->whereRaw("concat(firstname, ' ', lastname) ilike '%" . $requestdata['user'] . "%' ");
                }
            })
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['mobile']) && $requestdata['mobile'] != null) {
                    return $query->where('mobile', 'ilike', '%' . $requestdata['mobile'] . '%');
                }
            })
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['address']) && $requestdata['address'] != null) {
                    return $query->where('address', 'ilike', '%' . $requestdata['address'] . '%');
                }
            })
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['gender']) && $requestdata['gender'] != null) {
                    return $query->where('gender', 'ilike', '%' . $requestdata['gender'] . '%');
                }
            })
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['email']) && $requestdata['email'] != null) {
                    return $query->where('email', 'ilike', '%' . $requestdata['email'] . '%');
                }
            })
            ->where(function ($query) use ($requestdata) {
                if (isset($requestdata['status']) && $requestdata['status'] != null) {
                    return $query->where('status', 'ilike', '%' . $requestdata['status'] . '%');
                }
            })
            ->get();

            $user = $user->map(function ($user) {
                return $user->format($user);
            });
            if ($request->has('order')) {
                $coulmn =$this->users->searchCoulmns[$requestdata['index']];
                if ($requestdata['orderby'] == 'asc') {
                    $user = $user->sortByDesc($coulmn);
                } else {
                    $user = $user->sortBy($coulmn);
                }
            } else {
                $user = $user;
            }


            if ($request->has('start') && $request->has('length')) {
                $user = $this->paginate($user, $request->start, $request->length);
            }
            if (isset($user) && count($user) > 0) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $user,
                    trans('messages.success'),
                    trans('messages.user_list_are_fetch_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.not_able_to_get_fetch_users'),
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
    *       path="/api/users",
    *       tags={"Users"},
    *       summary="Store a newly created users in storage",
    *       @OA\RequestBody(
    *           required=true,
    *           @OA\JsonContent(ref="#/components/schemas/UserRequest")
    *       ),
    *       @OA\Response(
    *           response="200", description="Users Added successfully"
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
            $validator = Validator::make(
                $request->all(),
                [
            'firstname'         => 'required|string|max:191',
            'lastname'          => 'required|string|max:191',
            'email'             => 'required|email:rfc,dns|unique:users,email',
            'gender'            => 'required',
            'mobile'            => 'required|max:10|min:10|unique:users,mobile',
            'address'           => 'required',
            'status'            => 'required',
            'password'          => 'required|min:6|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!@$*#%]).*$/',
            'confirm_password' => ['same:password'],
            ],
                [
                'password.required' =>  __('passwords.password_required'),
                'password.min'      =>  __('passwords.password_validate_message'),
                'password.regex'    =>  __('passwords.password_validate_message'),
            ]
            );

            $request['password'] = bcrypt($request->password);
            $request['firstname'] = strtolower($request['firstname']);
            $request['lastname'] = strtolower($request['lastname']);
            $request['email'] = strtolower($request['email']);
            $request['genderr'] = trim(strtoupper($request->gender));

            if ($validator->fails()) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_BAD_REQUEST'),
                    $validator->errors(),
                    trans('messages.error'),
                    trans('messages.validation_error'),
                );
            }
            $request['ip_address']  = $request->ip();
            $request['gender'] = trim(strtoupper($request->gender));
            $user = User::create($request->all());
            if (isset($user) && $user != '') {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $user,
                    trans('messages.success'),
                    trans('messages.user_add_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.not_able_to_add_user'),
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
    *       path="/api/users/{user}",
    *       tags={"Users"},
    *       summary="Display the specified users",
    *       @OA\Response(
    *           response="200", description="Users fetch successfully"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */
    public function show(User $user)
    {
        try {
            if ($user) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $user,
                    trans('messages.success'),
                    trans('messages.user_fetch_successfully'),
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
     *       path="/api/users/{user}",
     *       tags={"Users"},
     *       summary="Update users in storage",
     *       @OA\RequestBody(
     *           required=true,
     *           @OA\JsonContent(ref="#/components/schemas/UserRequest")
     *       ),
     *       @OA\Response(
     *           response="200", description="Users  Updated successfully"
     *       ),
     *       @OA\Response(
     *           response="400", description="Validation error"
     *       ),
     *       @OA\Response(
     *           response="500", description="Internal server error"
     *       )
     *   )
     */
    public function update(Request $request, User $user)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
            'firstname'         => 'required|string|max:191',
            'lastname'          => 'required|string|max:191',
            'email'             => 'required|email:rfc,dns|unique:users,email,'.$user->id,
            'gender'            => 'required',
            'mobile'            => 'required|max:10|min:10|unique:users,mobile,'.$user->id,
            'address'           => 'required',
            'status'            => 'required',
            ]
            );

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
            $request['email'] = strtolower($request['email']);
            $request['gender'] = trim(strtoupper($request->gender));
            if (isset($request['password']) && $request['password']!= null && $request['password'] !='') {
                $request['password'] = bcrypt($request->password);
            } else {
                $request['password'] = $user->password;
            }
            $users = $user->update($request->all());
            if ($users) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $users,
                    trans('messages.success'),
                    trans('messages.user_update_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.user_not_updated'),
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
    *       path="/api/users/{user}",
    *       tags={"Users"},
    *       summary="Delete Users in storage",
    *       @OA\Response(
    *           response="200", description="Users Deleted successfully"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */
    public function destroy(User $user)
    {
        try {
            $user->delete();
            if ($user) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $user,
                    trans('messages.success'),
                    trans('messages.user_deleted_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.user_not_deleted'),
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
    *       path="/api/change-password",
    *       tags={"User"},
    *       summary="ChangePassword process",
    *       @OA\RequestBody(
    *           required=true,
    *           @OA\JsonContent(ref="#/components/schemas/ChangePasswordRequest")
    *       ),
    *       @OA\Response(
    *           response="200", description="Password successfully changed!"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */

    public function changePassword(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
            'current_password' => 'required',
            'new_password'     => 'required|min:6|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!@$*#%]).*$/',
            'confirm_password' => ['same:new_password'],
            ],
            [
                'password.required' =>  __('passwords.password_required'),
                'password.min'      =>  __('passwords.password_validate_message'),
                'password.regex'    =>  __('passwords.password_validate_message'),
            ]
        );

        if ($validator->fails()) {
            return $this->resultResponse(
                Config::get('rest.http_status_codes.HTTP_BAD_REQUEST'),
                $validator->errors(),
                trans('messages.error'),
                trans('messages.validation_error'),
            );
        }
        if (!(Hash::check($request->get('current_password'), auth('api')->user()->password))) {
            return $this->resultResponse(
                Config::get('rest.http_status_codes.HTTP_BAD_REQUEST'),
                $this->emptyArrayObject,
                trans('messages.error'),
                trans('messages.your_current_password_does_not_matches_with_the_password.'),
            );
        }

        $user= User::find(auth('api')->user()->id)->update(['password'=> Hash::make($request->new_password)]);
        if ($user) {
            return $this->resultResponse(
                Config::get('rest.http_status_codes.HTTP_BAD_REQUEST'),
                $this->emptyArrayObject,
                trans('messages.success'),
                trans('messages.password_successfully_changed!'),
            );
        }
    }

    /**
    * @OA\Get(
    *       path="/api/getProfile",
    *       tags={"Users"},
    *       summary="Display the Profile of  users",
    *       @OA\Response(
    *           response="200", description="Users fetch successfully"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */


    public function getUserProfile()
    {
        try {
            $user = Auth::user();
            $users = User::with('role')->where('id', $user->id)->first();
            if ($users) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $users,
                    trans('messages.success'),
                    trans('messages.user_Profile_are_fetch_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.not_able_to_get_fetch_users_profile'),
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
     *       path="/api/updateProfile",
     *       tags={"Users"},
     *       summary="Update users profile in storage",
     *       @OA\RequestBody(
     *           required=true,
     *           @OA\JsonContent(ref="#/components/schemas/UserRequest")
     *       ),
     *       @OA\Response(
     *           response="200", description="Users profile Updated successfully"
     *       ),
     *       @OA\Response(
     *           response="400", description="Validation error"
     *       ),
     *       @OA\Response(
     *           response="500", description="Internal server error"
     *       )
     *   )
     */
    public function updateUserProfile(Request $request)
    {
        try {
            $user = auth()->user();
            $validator = Validator::make(
                $request->all(),
                [
            'firstname'         => 'required|string|max:191',
            'lastname'          => 'required|string|max:191',
            'email'             => 'required|email:rfc,dns|unique:users,email,'.$user->id,
            'gender'            => 'required',
            'mobile'            => 'required|max:10|min:10|unique:users,mobile,'.$user->id,
            'address'           => 'required',
            'status'            => 'required',
            ]
            );
            if ($validator->fails()) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_BAD_REQUEST'),
                    $validator->errors(),
                    trans('messages.error'),
                    trans('messages.validation_error'),
                );
            }
            $request['gender'] = trim(strtoupper($request->gender));
            if (isset($request['password']) && $request['password']!= null && $request['password'] !='') {
                $request['password'] = bcrypt($request->password);
            } else {
                $request['password'] = $user->password ;
            }

            $users = User::where('id', $user->id)->update($request->all());
            if ($users) {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_OK'),
                    $users,
                    trans('messages.success'),
                    trans('messages.user_profile_update_successfully'),
                );
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.user_profile_not_updated'),
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

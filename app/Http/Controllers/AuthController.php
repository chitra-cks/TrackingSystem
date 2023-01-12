<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\RestApi;
use App\Notifications\ResetForgetPassword;
use DB;
use Config;
use Validator;
use Auth;
use File;
use ArrayObject;
use Str;
use Notification;

class AuthController extends Controller
{
    use RestApi;

    /**
    * Create a new controller instance.
    * @return void
    */
    public function __construct()
    {
        $this->emptyArrayObject = new ArrayObject();
    }

    /**
    * @OA\Post(
    *		path="/api/register",
    *		tags={"Auth"},
    *		summary="Registration process",
    * 		@OA\RequestBody(
    *			required=true,
    *			@OA\JsonContent(ref="#/components/schemas/RegisterRequest")
    *		),
    *		@OA\Response(
    * 			response="200", description="User registration completed successfully"
    * 		),
    * 		@OA\Response(
    * 			response="400", description="Validation error"
    * 		),
    * 		@OA\Response(
    * 			response="500", description="Internal server error"
    * 		)
    * 	)
    */

    public function register(Request $request)
    {
        $requestData = $request->all();

        if (array_key_exists('email', $requestData)) {
            $requestData['email'] = strtolower(trim($requestData['email']));
        }
        if (array_key_exists('firstname', $requestData)) {
            $requestData['firstname'] = ucfirst(trim($requestData['firstname']));
        }
        if (array_key_exists('lastname', $requestData)) {
            $requestData['lastname'] = ucfirst(trim($requestData['lastname']));
        }
        if (array_key_exists('gender', $requestData)) {
            $requestData['gender'] = ucfirst(trim($requestData['gender']));
        }
        if (array_key_exists('mobile', $requestData)) {
            $requestData['mobile'] = ucfirst(trim($requestData['mobile']));
        }
        if (array_key_exists('address', $requestData)) {
            $requestData['address'] = ucfirst(trim($requestData['address']));
        }

        $validator = Validator::make(
            $request->all(),
            [
            'firstname' 		=> 'required|string|max:191',
            'lastname' 			=> 'required|string|max:191',
            'email' 			=> 'required|email:rfc,dns|unique:users,email',
            'gender'            =>'required',
            'mobile'            =>'required|max:10|min:10|unique:users,mobile',
            'address'            =>'required',
            'password'     		=> 'required|min:6|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!@$*#%]).*$/',
            'confirm_password'  => 'required|same:password'
            ],
            [
                'password.required' =>  __('passwords.password_required'),
                'password.min'      =>  __('passwords.password_validate_message'),
                'password.regex'    =>  __('passwords.password_validate_message'),
            ]
        );

        $requestData['password'] = bcrypt($request->password);

        if ($validator->fails()) {
            return $this->resultResponse(
                Config::get('rest.http_status_codes.HTTP_BAD_REQUEST'),
                $validator->errors(),
                trans('messages.error'),
                trans('messages.validation_error'),
            );
        }
        $requestData['ip_address']  = $request->ip();

        $user = User::create($requestData);

        return $this->resultResponse(
            Config::get('rest.http_status_codes.HTTP_OK'),
            $this->emptyArrayObject,
            trans('messages.success'),
            trans('messages.registration_successfull_completed'),
        );
    }

    /**
    * @OA\Post(
    *		path="/api/login",
    *		tags={"Auth"},
    *		summary="Login process",
    * 		@OA\RequestBody(
    *			required=true,
    *			@OA\JsonContent(ref="#/components/schemas/LoginRequest")
    *		),
    *		@OA\Response(
    * 			response="200", description="User login successfully"
    * 		),
    * 		@OA\Response(
    * 			response="400", description="Validation error"
    * 		),
    * 		@OA\Response(
    * 			response="500", description="Internal server error"
    * 		)
    * 	)
    */
    public function login(Request $request)
    {
        $requestData = $request->all();
        if (array_key_exists('email', $requestData)) {
            $requestData['email'] = strtolower(trim($requestData['email']));
        }

        $validator = Validator::make(
            $request->all(),
            [
            'email' 			=> 'required|email',
            'password'     		=> 'required',
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



        if (!auth()->attempt($requestData)) {
            return $this->resultResponse(
                Config::get('rest.http_status_codes.HTTP_BAD_REQUEST'),
                $this->emptyArrayObject,
                trans('messages.error'),
                trans('messages.invalid_user_pass'),
            );
        }


        if (!User::where('email', $requestData['email'])->where('status', 'enable')->exists()) {
            return $this->resultResponse(
                Config::get('rest.http_status_codes.HTTP_BAD_REQUEST'),
                $this->emptyArrayObject,
                trans('messages.error'),
                trans('messages.user_disable'),
            );
        }

        $accessToken = auth()->user()->createToken('authToken')->accessToken;

        return $this->resultResponse(
            Config::get('rest.http_status_codes.HTTP_OK'),
            ['user' => auth()->user(), 'access_token' => $accessToken],
            trans('messages.success'),
            trans('messages.login_success')
        );
    }


    /**
    * @OA\Post(
    *       path="/api/forgetpassword",
    *       tags={"Auth"},
    *       summary="ForgetPassword process",
    *       @OA\RequestBody(
    *           required=true,
    *           @OA\JsonContent(ref="#/components/schemas/ForgetPasswordRequest")
    *       ),
    *       @OA\Response(
    *           response="200", description="Send notification for reset password!"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="404", description="User not found"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */

    public function sendPasswordResetNotification(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                'email'             => 'required|email',
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
            $user = User::where('email', $request->email)->first();
            if (isset($user) && $user != '') {
                $token = base64_encode(Str::random(12));
                $email = $request->email;
                $update_user = User::where('id', $user->id)->update(['resetpassword_token'=>$token]);
                if ($update_user) {
                    Notification::send($user, new ResetForgetPassword($token, $email));
                    return $this->resultResponse(
                        Config::get('rest.http_status_codes.HTTP_OK'),
                        $update_user,
                        trans('messages.success'),
                        trans('messages.notification_for_reset_password_send_successfully'),
                    );
                } else {
                    return $this->resultResponse(
                        Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                        $this->emptyArrayObject,
                        trans('messages.error'),
                        trans('messages.notification_not_send'),
                    );
                }
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.user_not_registered'),
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
    *       path="/api/resetpassword",
    *       tags={"Auth"},
    *       summary="ResetPassword process",
    *       @OA\RequestBody(
    *           required=true,
    *           @OA\JsonContent(ref="#/components/schemas/ResetPasswordRequest")
    *       ),
    *       @OA\Response(
    *           response="200", description="Reset Password of a user!"
    *       ),
    *       @OA\Response(
    *           response="400", description="Validation error"
    *       ),
    *       @OA\Response(
    *           response="500", description="Internal server error"
    *       )
    *   )
    */

    public function resetpassword(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
            'email'             => 'required|email',
            'password'          => 'required|min:6|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!@$*#%]).*$/',
            'confirm_password'  => 'required|same:password',
            'token' => 'required|string'
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
            $password = bcrypt($request->password);
            $userdata = User::where('resetpassword_token', $request->token)->first();
            if (isset($userdata) && $userdata != '') {
                $user = User::where('resetpassword_token', $request->token)->update(['resetpassword_token'=>null,'password'=>$password]);
                if ($user) {
                    return $this->resultResponse(
                        Config::get('rest.http_status_codes.HTTP_OK'),
                        $user,
                        trans('messages.success'),
                        trans('messages.password_change_successfully'),
                    );
                } else {
                    return $this->resultResponse(
                        Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                        $this->emptyArrayObject,
                        trans('messages.error'),
                        trans('messages.password_not_change'),
                    );
                }
            } else {
                return $this->resultResponse(
                    Config::get('rest.http_status_codes.HTTP_NOT_FOUND'),
                    $this->emptyArrayObject,
                    trans('messages.error'),
                    trans('messages.password_already_change'),
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

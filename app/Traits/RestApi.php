<?php

namespace App\Traits;

use Response;
use Config;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * RestApi
 *
 * @package                Safe Health
 * @subpackage             RestApi
 * @category               Trait
 * @ShortDescription       This trait is responsible to Access the config of rest
                            and also generate the response for each request
 **/
trait RestApi
{
    /**
    * @ShortDescription      This function is responsible to get the rest_configration
    * @return                Array
    */
    protected function rest_config()
    {
        return Config::get('rest.rest_config');
    }

    /**
    * @ShortDescription      This function is responsible to get the http_status codes
    * @return                Array
    */
    protected function http_status_codes()
    {
        return Config::get('rest.http_status_codes');
    }

    /**
    * @ShortDescription      This function is responsible for getting full request data
    */
    protected function getRequestData($request)
    {
        $requestData = $request->all();
        $requestData['ip_address'] = $request->ip();
        return $requestData;
    }

    /**
    * @ShortDescription      This function is responsible for generating the resposnse for each array
    * @param                 Integer $code
                             Array $data
                             String $error - Default "Unknown Error"
                             String $msg
                             Integer $http_status - Default 3000
    * @return                Response (Submit attributes)
    */
    protected function resultResponse($code, $data, $errors = [], $msg, $callType = '', $redirectType = '', $redirectRoute = '')
    {
        $rest_status_field    =  Config::get('rest.rest_config.rest_status_field_name');
        $rest_data_field      =  Config::get('rest.rest_config.rest_data_field_name');
        $rest_message_field   =  Config::get('rest.rest_config.rest_message_field_name');
        $rest_error_field     =  Config::get('rest.rest_config.rest_error_field_name');
        $rest_http_status     =  Config::get('rest.rest_config.rest_http_status_field_name');
        $rest_config          =  $this->rest_config();

        if ($callType == Config::get('constants.API_PREFIX')) {
            if ($rest_config['rest_default_format'] == 'json') {
                $response = response()->json([
                    $rest_status_field => $code,
                    $rest_data_field => $data,
                    $rest_message_field => $msg,
                    $rest_error_field => $errors
                ]);
            }
            if ($rest_config['rest_default_format'] == 'xml') {
                $response = response()->xml([
                    $rest_status_field => $code,
                    $rest_data_field => $data,
                    $rest_message_field => $msg,
                    $rest_error_field => $errors
                ]);
            }
            return $response;
        } else {
            if ($redirectType == 'forward' && $redirectRoute != '') {
                return redirect($redirectRoute)->with(array("message"=>$msg));
            } elseif ($redirectType == '' &&  $redirectRoute == '') {
                $response = response()->json([
                        $rest_status_field => $code,
                        $rest_data_field => $data,
                        $rest_message_field => $msg,
                        $rest_error_field => $errors
                    ]);
                return $response;
            } else {
                return redirect()->back()->withInput()->withErrors($errors);
            }
        }
    }

    protected function datasearch($jsondata)
    {
        try {
            $data = [];

            //Search Columns
            if (isset($jsondata['columns']) && $jsondata['columns'] != '') {
                foreach ($jsondata['columns'] as $columns) {
                    if (isset($columns['data'])) {
                        $data[$columns['data']] =  isset($columns['search']['value']) ? $columns['search']['value'] : null;
                    }
                }
            }
            //Order By Columns
            if (isset($jsondata['order']) && $jsondata['order'] != '') {
                foreach ($jsondata['order'] as $order) {
                    $data['index'] =  isset($order['column']) ? $order['column'] : null;
                    $data['orderby'] =  isset($order['dir']) ? $order['dir'] : null;
                }
            }

            return $data;
        } catch (\Exception $e) {
            return $this->resultResponse(
                Config::get('rest.http_status_codes.HTTP_BAD_REQUEST'),
                $e->getMessage(),
                trans('messages.error'),
                trans('messages.exception_error'),
            );
        }
    }
    protected function paginate($items, $start, $length)
    {
        try {
            $currentPage = LengthAwarePaginator::resolveCurrentPage();

            $currentPageCollection = $items->slice($start, $length)->reverse()->values();

            $paginated = new LengthAwarePaginator($currentPageCollection, count($items), $length);

            $paginated->setPath(LengthAwarePaginator::resolveCurrentPath());

            return $paginated;
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

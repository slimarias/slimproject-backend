<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Cartalyst\Sentinel\Laravel\Facades\Activation;
use Illuminate\Support\Facades\Auth;
use Modules\Help\Http\Controllers\HelpController;
use Modules\User\Http\Requests\CreateUserRequest;
use Modules\User\Http\Requests\UpdateUserRequest;
use Modules\User\Repositories\UserRepository;
use Modules\User\Transformers\UserTransformer;

class UserController extends HelpController
{
    protected $user;
    public function __construct(UserRepository $user){
        $this->user = $user;
    }

    /**
     * GET ITEMS
     *
     * @return mixed
     */
    public function index(Request $request)
    {
        try {
            //Validate permissions
            $this->validatePermission($request, 'profile.user.index');

            //Get Parameters from URL.
            $params = $this->getParamsRequest($request);

            //Add Id of users by department
            if (isset($params->filter->getUsersByDepartment))
                $params->usersByDepartment = $this->getUsersByDepartment($params);

            //Request to Repository
            $users = $this->user->getMany($params);

            //Response
            $response = ["data" => UserTransformer::collection($users)];

            //If request pagination add meta-page
            $params->page ? $response["meta"] = ["page" => $this->pageTransformer($users)] : false;
        } catch (\Exception $e) {
            $status = $this->getStatusError($e->getCode());
            $response = ["errors" => $e->getMessage()];
        }

        //Return response
        return response()->json($response, $status ?? 200);
    }

    /**
     * GET A ITEM
     *
     * @param $criteria
     * @return mixed
     */
    public function show($criteria, Request $request)
    {
        try {
        //Get Parameters from URL.
            $params = $this->getParamsRequest($request);

            //Request to Repository
            $user = $this->user->getOne($criteria, $params);

            //Break if no found item
            if (!$user) throw new \Exception('Item not found', 400);

            //Response
            $response = ["data" => new UserTransformer($user)];

            //If request pagination add meta-page
            $params->page ? $response["meta"] = ["page" => $this->pageTransformer($user)] : false;
        } catch (\Exception $e) {
            $status = $this->getStatusError($e->getCode());
            $response = ["errors" => $this->getErrorMessage($e)];
        }

        //Return response
        return response()->json($response, $status ?? 200);
    }

    /**
     * Register users just default department and role
     *
     * @param Request $request
     * @return mixed
     */
    public function register(Request $request)
    {
        try {
            $data = (object)$request->input('attributes');//Get data from request

            $filter = [];//define filters
            $validateEmail = config('user.validateRegisterWithEmail');

            //TODO: validate register for admin > slim
            $adminNeedsToActivateNewUsers = config('user.adminNeedsToActivateNewUsers');

            //Validate custom Request user
            $this->validateRequestApi(new CreateUserRequest((array)$data));

            $attributes = array_merge($request->input('attributes'),[
                'first_name' => $data->first_name ?? '',
                'last_name' => $data->last_name ?? '',
                'email' => $data->email,
                'password' => $data->password,
                'password_confirmation' => $data->password_confirmation,
                'departments' => [1],//Default department is USERS, ID 1
                'roles' => $role ?? [2],//Default role is USER, ID 2
                'fields' => $fields ?? [],
                'is_activated' => (int)$validateEmail ? false : true
            ],);
            //Format dat ot create user
            $params = [
                'attributes' => $attributes,
                'filter' => json_encode([
                'checkEmail' => (int)$validateEmail ? 1 : 0,
                'checkAdminActivate' => (int)$adminNeedsToActivateNewUsers ? 1 : 0,
                ])
            ];

            //Create user
            $user = $this->validateResponseApi($this->create(new Request($params)));

            //Response and especific if user required check email
            $response = ["data" => ['checkEmail' => (int)$validateEmail ? true : false]];
        } catch (\Exception $e) {
            \DB::rollback();//Rollback to Data Base
            $status = $this->getStatusError($e->getCode());
            $response = ["errors" => $e->getMessage()];
        }

        //Return response
        return response()->json($response ?? ["data" => "Request successful"], $status ?? 200);
    }

    /**
     * CREATE A ITEM
     *
     * @param Request $request
     * @return mixed
     */
    public function create(Request $request)
    {
        \DB::beginTransaction();
        try {
            //Validate permissions
            $this->validatePermission($request, 'profile.user.create');

            //Get data
            $data = $request->input('attributes');

            $data["email"] = strtolower($data["email"]);//Parse email to lowercase
            $params = $this->getParamsRequest($request);
            $checkEmail = isset($params->filter->checkEmail) ? $params->filter->checkEmail : false;
            $checkAdminActivate = isset($params->filter->checkAdminActivate) ? $params->filter->checkAdminActivate : false; //TODO: check if admin needs to activate new users > slim

            //$this->validateRequestApi(new RegisterRequest ($data));//Validate Request User
            $this->validateRequestApi(new CreateUserRequest($data));//Validate custom Request user

            if ($checkEmail) { //Create user required validate email
                $user = app(UserRegistration::class)->register($data);
            } else { //Create user activated
                $user = $this->userRepository->createWithRoles($data, $data["roles"], $data["is_activated"]);
            }

            if ($checkAdminActivate) {
                $this->update($user->id, new Request(
                ['attributes' => [
                    'id' => $user->id,
                    'is_activated' => false
                ]]
                ));
            }

            // sync tables
            if (isset($data["departments"]) && count($data["departments"])) {
                $user->departments()->sync(\Arr::get($data, 'departments', []));
            }

            //Create fields
            if (isset($data["fields"]))
                foreach ($data["fields"] as $field) {
                $field['user_id'] = $user->id;// Add user Id
                $this->validateResponseApi(
                    $this->field->create(new Request(['attributes' => (array)$field]))
                );
                }

            //Create Addresses
            if (isset($data["addresses"]))
                foreach ($data["addresses"] as $address) {
                $address['user_id'] = $user->id;// Add user Id
                $this->validateResponseApi(
                    $this->address->create(new Request(['attributes' => (array)$address]))
                );
                }

            //Create Settings
            if (isset($data["settings"])) {
                foreach ($data["settings"] as $settingName => $setting) {
                $this->validateResponseApi(
                    $this->setting->create(new Request(['attributes' =>
                    ['related_id' => $user->id, 'entity_name' => 'user', 'name' => $settingName, 'value' => $setting]
                    ]))
                );
                }
            }

            $response = ["data" => "User Created"];

            //dispatch Event

            \DB::commit(); //Commit to Data Base
        } catch (\Exception $e) {
            \DB::rollback();//Rollback to Data Base
            $status = $this->getStatusError($e->getCode());
            $response = ["errors" => $e->getMessage()];
        }

        //Return response
        return response()->json($response, $status ?? 200);
    }

    /**
     * UPDATE ITEM
     *
     * @param $criteria
     * @param Request $request
     * @return mixed
     */
    public function update($criteria, Request $request)
    {
        \DB::beginTransaction(); //DB Transaction
        try {
            //Validate permissions
            $this->validatePermission($request, 'profile.user.edit');
            $data = $request->input('attributes');//Get data
            $params = $this->getParamsRequest($request);//Get Params

            //Validate Request
            $this->validateRequestApi(new UpdateUserRequest((array)$data));

            if (isset($data["email"])) {
                $data["email"] = strtolower($data["email"]);
                $user = $this->user->findByAttributes(["email" => $data["email"]]);
            }

            if (!isset($user) || !$user || ($user->id == $data["id"])) {
                $user = $this->user->findByAttributes(["id" => $data["id"]]);
                $oldData = $user->toArray();

                // configuting activate data to audit
                if (Activation::completed($user) && !$data['is_activated'])
                $oldData['is_activated'] = 1;
                if (!Activation::completed($user) && $data['is_activated'])
                $oldData['is_activated'] = 0;

                // actually user roles
                $userRolesIds = $user->roles()->get()->pluck('id')->toArray();
                $this->userRepository->updateAndSyncRoles($data["id"], $data, []);
                $user = $this->user->findByAttributes(["id" => $data["id"]]);

                // saving old passrond
                if (isset($data["password"]))
                $oldData["password"] = $user->password;

                if (isset($data["roles"])) {
                // check roles to Attach and Detach
                $rolesToAttach = array_diff(array_values($data['roles']), $userRolesIds);
                $rolesToDetach = array_diff($userRolesIds, array_values($data['roles']));

                // sync roles
                if (!empty($rolesToAttach)) {
                    $user->roles()->attach($rolesToAttach);
                }
                if (!empty($rolesToDetach)) {
                    $user->roles()->detach($rolesToDetach);
                }
                }

                if (isset($data['departments'])) {
                // actually user departments
                $userDepartmentsIds = $user->departments()->get()->pluck('id')->toArray();

                // check departments to Attach and Detach
                $departmentsToAttach = array_diff(array_values($data['departments']), $userDepartmentsIds);
                $departmentsToDetach = array_diff($userDepartmentsIds, array_values($data['departments']));


                // sync departments
                if (!empty($departmentsToAttach)) {
                    $user->departments()->attach($departmentsToAttach);
                }
                if (!empty($departmentsToDetach)) {
                    $user->departments()->detach($departmentsToDetach);
                }
                }

                //Create or Update fields
                if (isset($data["fields"]))
                foreach ($data["fields"] as $field) {
                    if (is_bool($field["value"]) || (isset($field["value"]) && !empty($field["value"]))) {
                    $field['user_id'] = $user->id;// Add user Id
                    if (!isset($field["id"])) {
                        $this->validateResponseApi(
                        $this->field->create(new Request(['attributes' => (array)$field]))
                        );
                    } else {
                        $this->validateResponseApi(
                        $this->field->update($field["id"], new Request(['attributes' => (array)$field]))
                        );
                    }

                    } else {
                    if (isset($field['id'])) {
                        $this->validateResponseApi(
                        $this->field->delete($field['id'], new Request(['attributes' => (array)$field]))
                        );
                    }
                    }
                }

                //Create or Update Addresses
                if (isset($data["addresses"]))
                foreach ($data["addresses"] as $address) {
                    $address['user_id'] = $user->id;// Add user Id
                    if (!isset($address['id']))
                    $this->validateResponseApi(
                        $this->address->create(new Request(['attributes' => (array)$address]))
                    );
                    else
                    $this->validateResponseApi(
                        $this->address->update($address['id'], new Request(['attributes' => (array)$address]))
                    );

                }


                //Create or Update Settings
                if (isset($data["settings"]))
                foreach ($data["settings"] as $settingName => $setting) {
                    $this->profileSetting->updateOrCreate(
                    ['related_id' => $user->id, 'entity_name' => 'user', 'name' => $settingName],
                    ['related_id' => $user->id, 'entity_name' => 'user', 'name' => $settingName, 'value' => $setting]
                    );
                }

                // configuring pasword to audit
                if (isset($data["password"]))
                $data["password"] = $user->password;

                //Response
                $response = ["data" => $user];
            } else {
                $status = 400;
                $response = ["errors" => $data["email"] . ' | User Name already exist'];
            }

            \DB::commit();//Commit to DataBase
        } catch (\Exception $e) {
            \DB::rollback();//Rollback to Data Base
            $status = $this->getStatusError($e->getCode());
            $response = ["errors" => $e->getMessage()];
        }

        //Return response
        return response()->json($response, $status ?? 200);
    }

    /**
     * Change password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        \DB::beginTransaction(); //DB Transaction
        try {
            //Auth api controller
            $authApiController = app('Modules\User\Http\Controllers\AuthController');
            $requestLogout = new Request();

            //Get Parameters from URL.
            $params = $request->input('attributes');

            //Try to login and Get Token
            $token = $this->validateResponseApi($authApiController->authAttempt($params));
            $requestLogout->headers->set('Authorization', $token->bearer);//Add token to headers
            $user = Auth::user();//Get User

            //Check if password exist in history
            $usedPassword = $this->validateResponseApi($authApiController->checkPasswordHistory($params['newPassword']));

            //Update password
            $userUpdated = $this->validateResponseApi(
                $this->update($user->id, new request(
                [
                    'attributes' => [
                        'password' => $params['newPassword'],
                        'id' => $user->id,
                        'is_activated' => true
                    ]
                ]
                ))
            );

            //Logout token
            $this->validateResponseApi($authApiController->logout($requestLogout));

            //response with userId
            $response = ['data' => ['userId' => $user->id]];
            \DB::commit();//Commit to DataBase
        } catch (\Exception $e) {
            \DB::rollback();//Rollback to Data Base
            $status = $this->getStatusError($e->getCode());
            $response = ["errors" => $e->getMessage()];
        }

        //Return response
        return response()->json($response ?? ["data" => "Password updated"], $status ?? 200);

    }

    /**
     * Upload media files
     *
     * @param Request $request
     * @return mixed
     */
    public function mediaUpload(Request $request)
    {
        try {
            $auth = Auth::user();
            $data = $request->all();//Get data
            $user_id = $data['user'];
            $name = $data['nameFile'];
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $nameFile = $name . '.' . $extension;
            $allowedextensions = array('JPG', 'JPEG', 'PNG', 'GIF', 'ICO', 'BMP', 'PDF', 'DOC', 'DOCX', 'ODT', 'MP3', '3G2', '3GP', 'AVI', 'FLV', 'H264', 'M4V', 'MKV', 'MOV', 'MP4', 'MPG', 'MPEG', 'WMV');
            $destination_path = 'assets/iprofile/profile/files/' . $user_id . '/' . $nameFile;
            $disk = 'publicmedia';
            if (!in_array(strtoupper($extension), $allowedextensions)) {
                throw new Exception(trans('iprofile::profile.messages.file not allowed'));
            }
            if ($user_id == $auth->id || $auth->hasAccess('user.users.create')) {

                if (in_array(strtoupper($extension), ['JPG', 'JPEG'])) {
                $image = \Image::make($file);

                \Storage::disk($disk)->put($destination_path, $image->stream($extension, '90'));
                } else {

                \Storage::disk($disk)->put($destination_path, \File::get($file));
                }

                $status = 200;
                $response = ["data" => ['url' => $destination_path]];


            } else {
                $status = 403;
                $response = [
                'error' => [
                    'code' => '403',
                    "title" => trans('iprofile::profile.messages.access denied'),
                ]
                ];
            }

            } catch (\Exception $e) {
                \Log::error($e->getMessage());
                $status = $this->getStatusError($e->getCode());
                $response = ["errors" => $e->getMessage()];
            }

            return response()->json($response ?? ["data" => "Request successful"], $status ?? 200);
        }

        /**
         * delete media
         *
         * @param Request $request
         * @return mixed
         */
        public function mediaDelete(Request $request)
        {
            try {
            $disk = "publicmedia";
            $auth = Auth::user();
            $data = $request->all();//Get data
            $user_id = $data['user'];
            $dirdata = $request->input('file');

            if ($user_id == $auth->id || $auth->hasAccess('user.users.create')) {

                \Storage::disk($disk)->delete($dirdata);

                $status = 200;
                $response = [
                'susses' => [
                    'code' => '201',
                    "source" => [
                    "pointer" => url($request->path())
                    ],
                    "title" => trans('core::core.messages.resource delete'),
                    "detail" => [
                    ]
                ]
                ];
            } else {
                $status = 403;
                $response = [
                'error' => [
                    'code' => '403',
                    "title" => trans('iprofile::profile.messages.access denied'),
                ]
                ];
            }

        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            $status = $this->getStatusError($e->getCode());
            $response = ["errors" => $e->getMessage()];
        }

        return response()->json($response ?? ["data" => "Request successful"], $status ?? 200);
    }
}

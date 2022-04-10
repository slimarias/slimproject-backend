<?php

namespace Modules\Help\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class HelpController extends Controller
{
  private $permissions;
  private $permissionsController;
  private $user;
    //Return params from Request
  public function getParamsRequest($request, $params = [])
  {
    $defaultValues = (object)$params;//Convert to object the params
    //$this->settingsController = new SettingsApiController();
    $this->permissionsController = new PermissionController();

    //Set default values
    $default = (object)[
      "page" => $defaultValues->page ?? false,
      "take" => $defaultValues->take ?? false,
      "filter" => $defaultValues->filter ?? [],
      'include' => $defaultValues->include ?? [],
      'fields' => $defaultValues->fields ?? []
    ];

    // set current auth user
    $this->user = \Sentinel::getUser();
    $setting = $request->input('setting') ? (is_string($request->input('setting')) ? json_decode($request->input('setting')) : (is_array($request->input('setting')) ? json_decode(json_encode($request->input('setting'))) : $request->input('setting'))) : false;

    //$departments = $this->user ? $this->user->departments()->get() : false;//Department data
    $roles = $this->user ? $this->user->roles()->get() : false;//Role data
    /*$department = ($departments && $setting && isset($setting->departmentId)) ?
      $departments->where("id", $setting->departmentId)->first() : false;*/
    $role = ($roles && $setting && isset($setting->roleId)) ? $roles->where("id", $setting->roleId)->first() : false;

    //Return params
    $params = (object)[
      "order" => $request->input('order') ? json_decode($request->input('order')) : null,
      "page" => is_numeric($request->input('page')) ? $request->input('page') : $default->page,
      "take" => is_numeric($request->input('take')) ? $request->input('take') :
        ($request->input('page') ? 12 : $default->take),
      "filter" => !$request->input('filter') ? (object)$default->filter :
        (is_string($request->input('filter')) ? json_decode($request->input('filter')) : json_decode(json_encode($request->input('filter')))),
      "include" => $request->input('include') ? explode(",", $request->input('include')) : $default->include,
      "fields" => $request->input('fields') ? explode(",", $request->input('fields')) : $default->fields,
      /*'department' => $department,
      'departments' => $departments,*/
      'role' => $role,
      'roles' => $roles,
      'setting' => $setting,//Role and department selected
      /*'settings' => $this->user ? $this->settingsController->getAll([
        "userId" => $this->user->id,
        "roleId" => $role->id ?? false,
        "departmentId" => $department->id ?? false]) : [],*/
      'permissions' => $this->user ? $this->permissionsController->getAll([
        "userId" => $this->user->id,
        "roleId" => $role->id ?? false,
      ]) : [],
      "user" => $this->user
    ];

    //Set language translation
    if (isset($setting->locale) && !is_null($setting->locale)) {
      \App::setLocale($setting->locale);
    }

    //Set language translation by filter
    if (isset($params->filter->locale) && !is_null($params->filter->locale)) {
      \App::setLocale($params->filter->locale);
    }

    //Set locale to filter
    $params->filter->locale = \App::getLocale();
    return $params;//Response
  }
  //Validate if code is like status response, and return status code
  public function getStatusError($code = false)
  {
    switch ($code) {
      case 204:
        return 204;
        break;
      case 400: //Bad Request
        return 400;
        break;
      case 401:
        return 401;
        break;
      case 403:
        return 403;
        break;
      case 404:
        return 404;
        break;
      case 502:
        return 502;
        break;
      case 504:
        return 504;
        break;
      default:
        return 500;
        break;
    }
  }

  //Validate if code is like status response, and return status code
  public function getErrorMessage(\Exception $e): string
  {
    if (env('APP_DEBUG') == true) {
      return $e->getMessage() . "\n" . $e->getFile() . "\n" . $e->getLine() . $e->getTraceAsString();
    } else {
      return $e->getMessage();
    }
  }

  public function validateResponseApi($response)
  {
    //Get response
    $data = json_decode($response->content());

    //If there is errors, throw error
    if (isset($data->errors)) {
      throw new \Exception($data->errors, $response->getStatusCode());
    } else {//if response is successful, return response
      return $data->data;
    }
  }

  //Validate if fields are validated according to rules
  public function validateRequestApi($request)
  {
    $request->setContainer(app());
    if (method_exists($request, "getValidator"))
      $validator = $request->getValidator();
    else
      $validator = \Validator::make($request->all(), $request->rules(), $request->messages());

    //if get errors, throw errors
    if ($validator->fails()) {
      $errors = json_decode($validator->errors());
      throw new \Exception(json_encode($errors), 400);
    } else {//if vlaidation is sucessful, return true
      return true;
    }
  }

  //Validate if user has permission
  public function validatePermission($request, $permissionName)
  {
    //Get permissions
    $this->permissionsController = new PermissionController();
    $permissions = $this->permissionsController->getAll($request);

    //Validate permissions
    if ($permissions && !isset($permissions[$permissionName]))
      throw new \Exception('Permission Denied', 403);
  }

}
